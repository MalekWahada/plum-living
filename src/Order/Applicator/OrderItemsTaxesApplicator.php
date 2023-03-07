<?php

declare(strict_types=1);

namespace App\Order\Applicator;

use App\Entity\Order\Adjustment;
use Psr\Log\LoggerInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Distributor\IntegerDistributorInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Taxation\Applicator\OrderTaxesApplicatorInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Webmozart\Assert\Assert;

class OrderItemsTaxesApplicator implements OrderTaxesApplicatorInterface
{
    private CalculatorInterface $calculator;
    private AdjustmentFactoryInterface $adjustmentFactory;
    private IntegerDistributorInterface $distributor;
    private TaxRateResolverInterface $taxRateResolver;
    private LoggerInterface $logger;

    public function __construct(
        CalculatorInterface $calculator,
        AdjustmentFactoryInterface $adjustmentFactory,
        IntegerDistributorInterface $distributor,
        TaxRateResolverInterface $taxRateResolver,
        LoggerInterface $logger
    ) {
        $this->calculator = $calculator;
        $this->adjustmentFactory = $adjustmentFactory;
        $this->distributor = $distributor;
        $this->taxRateResolver = $taxRateResolver;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     * @param ZoneInterface $zone
     */
    public function apply(OrderInterface $order, ZoneInterface $zone): void
    {
        foreach ($order->getItems() as $item) {
            $quantity = $item->getQuantity();
            Assert::notSame($quantity, 0, 'Cannot apply tax to order item with 0 quantity.');

            $taxRate = $this->taxRateResolver->resolve($item->getVariant(), ['zone' => $zone]);

            if (null === $taxRate) {
                continue;
            }

            // OrderItem total tax amount may be different in the digits after decimal point
            // compared to the multiplication of a unit tax amount by quantity.
            // e.g.: let's say we have:
            // - an order item having a quantity of 20 and its unit price is for 33.33 €
            // - 20% as VAT
            // the total tax amount of the order item() ---> 799.99 €
            // the sum of the unit tax amounts ---> 800.00 €
            // As we render unit items in the e-shop, whenever we update cart we need always
            // to calculate total tax amount based on the multiplication of the quantity by the single unit tax amount.
            $unitItemTaxAmount = $this->calculator->calculate($item->getFullDiscountedUnitPrice(), $taxRate);
            $totalTaxAmount = $unitItemTaxAmount * $quantity;

            $splitTaxes = $this->distributor->distribute($totalTaxAmount, $quantity);
            $i = 0;
            /** @var OrderItemUnitInterface $unit */
            foreach ($item->getUnits() as $unit) {
                if (!isset($splitTaxes[$i])) {
                    $this->logger->warning(
                        '[ADJUSTMENTS] Index not defined',
                        [
                            'split_taxes' => $splitTaxes,
                            'index' => $i,
                            'unit' => $item->getUnits()
                        ]
                    );
                    break;
                }

                if (0 === $splitTaxes[$i]) {
                    $this->logger->warning(
                        '[ADJUSTMENTS] Index === 0',
                        [
                            'split_taxes' => $splitTaxes,
                            'index' => $i,
                            'unit' => $item->getUnits()
                        ]
                    );
                    continue;
                }

                $this->addAdjustment($unit, $splitTaxes[$i], $taxRate->getLabel(), $taxRate->isIncludedInPrice());
                ++$i;
            }
        }
    }

    private function addAdjustment(OrderItemUnitInterface $unit, int $taxAmount, string $label, bool $included): void
    {
        $unitTaxAdjustment = $this->adjustmentFactory
            ->createWithData(AdjustmentInterface::TAX_ADJUSTMENT, $label, $taxAmount, $included)
        ;

        if ($unit->getAdjustments()->exists(
            static fn (int $key, BaseAdjustmentInterface $adjustment): bool =>
                $adjustment->getType() === AdjustmentInterface::TAX_ADJUSTMENT
                && $taxAmount === $adjustment->getAmount()
        )) {
            $this->logger->critical(
                sprintf(
                    'Duplicate adjustment detected for order "%d".',
                    $unit->getOrderItem()->getOrder()->getId()
                ),
                [
                    'adjustmentAmount' => $taxAmount,
                    'adjustmentType' => AdjustmentInterface::TAX_ADJUSTMENT,
                ]
            );
            return;
        }

        $unit->addAdjustment($unitTaxAdjustment);
    }
}
