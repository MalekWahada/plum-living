<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer;

use ApiPlatform\Core\Serializer\ItemNormalizer;
use App\Calculator\OrderItemPriceTaxCalculator;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Formatter\Money\MoneyFormatter;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Webmozart\Assert\Assert;

final class OrderItemSerializer implements ContextAwareNormalizerInterface
{
    private ItemNormalizer $normalizer;
    private MoneyFormatter $moneyFormatter;
    private OrderItemPriceTaxCalculator $orderItemPriceTaxCalculator;

    public function __construct(ItemNormalizer $normalizer, MoneyFormatter $moneyFormatter, OrderItemPriceTaxCalculator $orderItemPriceTaxCalculator)
    {
        $this->normalizer = $normalizer;
        $this->moneyFormatter = $moneyFormatter;
        $this->orderItemPriceTaxCalculator = $orderItemPriceTaxCalculator;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof OrderItem;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        /** @var OrderItem $object */
        Assert::isInstanceOf($object, OrderItem::class);

        /** @var Order $order */
        $order = $object->getOrder();

        // Define variant current locale from order locale
        if (null !== $object->getVariant()) {
            $object->getVariant()->setCurrentLocale($order->getLocaleCode());
        }

        $data = $this->normalizer->normalize($object, $format, $context);
        \assert(is_array($data));

        // We need to get item's original price without promotions and because item "total" is already containing all adjustments we don't want, we need to recalculate it
        $itemTotalInclVat = $this->orderItemPriceTaxCalculator->calculate($object);

        $data['totalInclVat'] = $this->moneyFormatter->formatWithDecimals($itemTotalInclVat, 2);

        if (isset($data['unitPrice'])) {
            $data['unitPrice'] = $this->moneyFormatter->formatWithDecimals($data['unitPrice'], 2);
        }

        if (isset($data['adjustmentsTotal'])) {
            $data['adjustmentsTotal'] = $this->moneyFormatter->formatWithDecimals($data['adjustmentsTotal'], 2);
        }

        if (isset($data['adjustmentsTotalRecursively'])) {
            $data['adjustmentsTotalRecursively'] = $this->moneyFormatter->formatWithDecimals($data['adjustmentsTotalRecursively'], 2);
        }

        $data['variantId'] = $object->getVariant()->getId();

        return $data;
    }
}
