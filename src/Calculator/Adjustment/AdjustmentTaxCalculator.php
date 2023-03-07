<?php

declare(strict_types=1);

namespace App\Calculator\Adjustment;

use App\Entity\Order\Order;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxation\TaxRate;
use App\Provider\Tax\TaxZoneProvider;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Webmozart\Assert\Assert;

class AdjustmentTaxCalculator
{
    private CalculatorInterface $taxCalculator;
    private TaxRateResolverInterface $taxRateResolver;
    private TaxZoneProvider $taxZoneProvider;

    public function __construct(
        CalculatorInterface $taxCalculator,
        TaxRateResolverInterface $taxRateResolver,
        TaxZoneProvider $taxZoneProvider
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->taxRateResolver = $taxRateResolver;
        $this->taxZoneProvider = $taxZoneProvider;
    }

    public function calculate(
        Order $order,
        string $adjustmentType,
        ?array $context = [],
        ?TaxRate $taxRate = null
    ): int {
        // some orders with a cart state might not have items
        if ($order->getItems()->isEmpty()) {
            return 0;
        }

        Assert::notNull($order->getItems()->first());
        Assert::notNull($order->getItems()->first()->getVariant());

        $adjustmentAmount = $order->getAdjustmentsTotalRecursively($adjustmentType);

        if (null === $taxRate) {
            $zone = $context['zone'] ?? $this->taxZoneProvider->getTaxZone($order);
            if (null === $zone) {
                return $adjustmentAmount;
            }

            /** @var ProductVariant|null $firstOrderItemVariant */
            $firstOrderItemVariant = $order->getItems()->first()->getVariant();
            $taxRate = $this->taxRateResolver->resolve($firstOrderItemVariant, ['zone' => $zone]);

            if (null === $taxRate) {
                return $adjustmentAmount;
            }
        }

        $totalTaxAmount = $this->taxCalculator->calculate($adjustmentAmount, $taxRate);

        return $adjustmentAmount + intval($totalTaxAmount);
    }
}
