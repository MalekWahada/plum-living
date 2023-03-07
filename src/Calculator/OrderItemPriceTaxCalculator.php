<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Order\OrderItem;
use App\Entity\Taxation\TaxRate;
use App\Provider\Tax\TaxZoneProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Webmozart\Assert\Assert;

class OrderItemPriceTaxCalculator
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
        OrderItem $orderItem,
        ?array $context = [],
        ?TaxRate $taxRate = null
    ): int {
        Assert::notNull($orderItem->getVariant());
        Assert::notNull($orderItem->getOrder());
        /** @var OrderInterface $order */
        $order = $orderItem->getOrder();

        $price = $orderItem->getSubTotalWithoutAdjustments();

        if (null === $taxRate) {
            $zone = $context['zone'] ?? $this->taxZoneProvider->getTaxZone($order);
            if (null === $zone) {
                return $price;
            }

            $taxRate = $this->taxRateResolver->resolve($orderItem->getVariant(), ['zone' => $zone]);
            if (null === $taxRate) {
                return $price;
            }
        }

        $totalTaxAmount = $this->taxCalculator->calculate($price, $taxRate);

        return $price + intval($totalTaxAmount);
    }
}
