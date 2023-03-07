<?php

declare(strict_types=1);

namespace App\Calculator\Shipping;

use App\Entity\Shipping\ShippingMethod;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;

class ShippingTaxCalculator
{
    private CalculatorInterface $taxCalculator;
    private TaxRateResolverInterface $taxRateResolver;

    public function __construct(
        CalculatorInterface $taxCalculator,
        TaxRateResolverInterface $taxRateResolver
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->taxRateResolver = $taxRateResolver;
    }

    public function calculate(ShippingMethod $shippingMethod, int $shippingAmount): int
    {
        $rate = $this->taxRateResolver->resolve($shippingMethod);
        if ($rate === null) {
            return $shippingAmount;
        }
        return $shippingAmount + intval($this->taxCalculator->calculate($shippingAmount, $rate));
    }
}
