<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Taxation\TaxRate;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;

class ProductPriceTaxCalculator
{
    private ChannelContextInterface $channelContext;

    private ProductVariantPricesCalculatorInterface $productVariantPricesCalculator;

    private CalculatorInterface $taxCalculator;

    private TaxRateResolverInterface $taxRateResolver;

    public function __construct(
        ChannelContextInterface $channelContext,
        ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        CalculatorInterface $taxCalculator,
        TaxRateResolverInterface $taxRateResolver
    ) {
        $this->channelContext = $channelContext;
        $this->productVariantPricesCalculator = $productVariantPricesCalculator;
        $this->taxCalculator = $taxCalculator;
        $this->taxRateResolver = $taxRateResolver;
    }

    public function calculate(
        ProductVariantInterface $productVariant,
        ?array $context = [],
        ?TaxRate $taxRate = null
    ): int {
        /** @var ChannelInterface $channel */
        $channel = $context['channel'] ?? $this->channelContext->getChannel();
        $price = $this->productVariantPricesCalculator->calculate($productVariant, ['channel' => $channel]);

        return $this->applyTaxOnAmount($price, $productVariant, $channel, $context, $taxRate);
    }

    public function calculateOriginalPrice(
        ProductVariantInterface $productVariant,
        ?array $context = [],
        ?TaxRate $taxRate = null
    ): int {
        /** @var ChannelInterface $channel */
        $channel = $context['channel'] ?? $this->channelContext->getChannel();
        $originalPrice = $this->productVariantPricesCalculator->calculateOriginal($productVariant, ['channel' => $channel]);

        return $this->applyTaxOnAmount($originalPrice, $productVariant, $channel, $context, $taxRate);
    }

    private function applyTaxOnAmount(
        int $amount,
        ProductVariantInterface $productVariant,
        ChannelInterface $channel,
        ?array $context = [],
        ?TaxRate $taxRate = null
    ): int {
        if (null === $taxRate) {
            $zone = $context['zone'] ?? $channel->getDefaultTaxZone();
            $taxRate = $this->taxRateResolver->resolve($productVariant, ['zone' => $zone]);
            if (null === $taxRate) {
                return $amount;
            }
        }

        $totalTaxAmount = $this->taxCalculator->calculate($amount, $taxRate);

        return $amount + intval($totalTaxAmount);
    }
}
