<?php

declare(strict_types=1);

namespace App\Provider\Product;

use App\Calculator\ProductPriceTaxCalculator;
use App\Entity\Product\ProductVariant;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

class ProductVariantsTaxedPricesProvider
{
    private ProductPriceTaxCalculator $variantPriceTaxCalculator;

    public function __construct(ProductPriceTaxCalculator $variantPriceTaxCalculator)
    {
        $this->variantPriceTaxCalculator = $variantPriceTaxCalculator;
    }

    public function provideVariantsTaxedPrices(ProductInterface $product, ChannelInterface $channel): array
    {
        $variantsPrices = [];

        /** @var ProductVariant $variant */
        foreach ($product->getEnabledVariants() as $variant) {
            $variantsPrices[] = $this->constructOptionsMap($variant, $channel);
        }

        return $variantsPrices;
    }

    private function constructOptionsMap(ProductVariant $variant, ChannelInterface $channel): array
    {
        $optionMap = [];

        /** @var ProductOptionValueInterface $option */
        foreach ($variant->getOptionValues() as $option) {
            $optionMap[$option->getOptionCode()] = $option->getCode();
        }

        $price = $this->variantPriceTaxCalculator->calculate($variant, ['channel' => $channel]);
        $optionMap['value'] = $price;

        if ($this->variantPriceTaxCalculator instanceof ProductPriceTaxCalculator) {
            $originalPrice = $this->variantPriceTaxCalculator->calculateOriginalPrice($variant, ['channel' => $channel]);

            if ($originalPrice > $price) {
                $optionMap['original-price'] = $originalPrice;
            }
        }

        return $optionMap;
    }
}
