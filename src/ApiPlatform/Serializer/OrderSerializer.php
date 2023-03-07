<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer;

use ApiPlatform\Core\Exception\RuntimeException;
use ApiPlatform\Core\Serializer\ItemNormalizer;
use App\Calculator\Adjustment\AdjustmentTaxCalculator;
use App\Entity\Channel\Channel;
use App\Entity\Order\Order;
use App\Formatter\Money\MoneyFormatter;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Webmozart\Assert\Assert;
use function array_reduce;
use function round;

final class OrderSerializer implements ContextAwareNormalizerInterface
{
    public const ORDER_TOTAL_INCONSISTENCY_EVENT = 'app.api_order.total_inconsistency_error';

    private ItemNormalizer $normalizer;
    private MoneyFormatter $moneyFormatter;
    private EventDispatcherInterface $eventDispatcher;
    private AdjustmentTaxCalculator $adjustmentTaxCalculator;
    private bool $enableApiOrderInconsistencyError;

    public function __construct(
        ItemNormalizer $normalizer,
        MoneyFormatter $moneyFormatter,
        EventDispatcherInterface $eventDispatcher,
        AdjustmentTaxCalculator $adjustmentTaxCalculator,
        bool $enableApiOrderInconsistencyError
    ) {
        $this->normalizer = $normalizer;
        $this->moneyFormatter = $moneyFormatter;
        $this->eventDispatcher = $eventDispatcher;
        $this->adjustmentTaxCalculator = $adjustmentTaxCalculator;
        $this->enableApiOrderInconsistencyError = $enableApiOrderInconsistencyError;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Order;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Order $object */
        Assert::isInstanceOf($object, Order::class);

        /** @var ?Channel $channel */
        $channel = $object->getChannel();

        $data = $this->normalizer->normalize($object, $format, $context);
        \assert(is_array($data));

        if (isset($data['total'])) {
            $data['total'] = $this->moneyFormatter->formatWithDecimals($data['total'], 2);
        }

        if (isset($data['taxTotal'])) {
            $data['taxTotal'] = $this->moneyFormatter->formatWithDecimals($data['taxTotal'], 2);
        }

        if (isset($data['orderPromotionTotal'])) {
            $data['orderPromotionTotal'] = abs($this->moneyFormatter->formatWithDecimals(
                $object->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT) // Order promotion is applied after taxes (TTC)
                + $this->adjustmentTaxCalculator->calculate($object, AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT) // Item promotions are applied before taxes (HT) so we must add taxes
                + $this->adjustmentTaxCalculator->calculate($object, AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT), // Item promotions are applied before taxes (HT) so we must add taxes
                2
            ));
        }

        /** @var Order $object */
        $orderShippingTotal = $object->getShippingTotal();
        $data['shippingTotal'] = $this->moneyFormatter->formatWithDecimals($orderShippingTotal, 2);

        /**
         * Reduce order items into single item per variantId
         */
        if (isset($data['items'])) {
            $reduced = [];
            foreach ($data['items'] as $item) {
                if (array_key_exists($item['variantId'], $reduced)) {
                    $reduced[$item['variantId']]['quantity'] += $item['quantity'];
                    $reduced[$item['variantId']]['totalInclVat'] += $item['totalInclVat'];
                } else {
                    $reduced[$item['variantId']] = $item;
                }
            }
            $data['items'] = array_values($reduced); // Stored as a simple array
        }

        /**
         * Check if total prices are valid
         * Prices are rounded to cents for this test
         */
        if (isset($data['total'], $data['orderPromotionTotal'], $data['items'])) {
            $totalUnits = array_reduce($data['items'], static function ($acc, $item) {
                return $acc + ($item['totalInclVat'] ?? 0);
            }, 0);
            $sumUp = round($totalUnits + $data['shippingTotal'] - $data['orderPromotionTotal'], 2); // Sum of all items + shipping - promotions rounded to cents
            $orderTotal = round($data['total'], 2);
            $gap = abs($sumUp - $orderTotal);
            if ($gap > 0.0299) { // Gap should not be higher than 3 cents
                $this->eventDispatcher->dispatch( /** @phpstan-ignore-next-line  */
                    self::ORDER_TOTAL_INCONSISTENCY_EVENT,
                    new GenericEvent($object, [
                        "gap" => round($gap, 2),
                        "total" => $orderTotal,
                        "sumup" => $sumUp
                    ])
                );
                if ($this->enableApiOrderInconsistencyError) {
                    $errorMessage = sprintf(
                        'Order total inconsistency detected: %s #%s (order total: %s, sum up: %s, gap: %s)',
                        $object->getNumber(),
                        $object->getId(),
                        $orderTotal,
                        $sumUp,
                        $gap
                    );
                    throw new RuntimeException($errorMessage);
                }
            }
        }

        // Filter payments (must be stored as a simple array)
        if (isset($data['payments'])) {
            $data['payments'] = array_values(array_filter($data['payments'], static function (array $payment) {
                return $payment['state'] === PaymentInterface::STATE_COMPLETED;
            }));
        }

        $data['channelCode'] = null !== $channel ? $channel->getCode() : null;

        return $data;
    }
}
