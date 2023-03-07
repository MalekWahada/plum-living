<?php

declare(strict_types=1);

namespace App\Generator\Product;

use Sylius\Component\Product\Checker\ProductVariantsParityCheckerInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Generator\CartesianSetBuilder;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Resource\Exception\VariantWithNoOptionsValuesException;
use Webmozart\Assert\Assert;

/**
 * This generator is copied from sylius ProductVariantGenerator
 * Generate method accepts a list of static options values to restrict the generated variants.
 */
class ProductVariantConfigurableGenerator
{
    private ProductVariantFactoryInterface $productVariantFactory;

    private CartesianSetBuilder $setBuilder;

    private ProductVariantsParityCheckerInterface $variantsParityChecker;

    public function __construct(
        ProductVariantFactoryInterface $productVariantFactory,
        ProductVariantsParityCheckerInterface $variantsParityChecker
    ) {
        $this->productVariantFactory = $productVariantFactory;
        $this->setBuilder = new CartesianSetBuilder();
        $this->variantsParityChecker = $variantsParityChecker;
    }

    /**
     * @throws VariantWithNoOptionsValuesException
     */
    public function generate(ProductInterface $product, ?array $staticOptions = null): void
    {
        Assert::true($product->hasOptions(), 'Cannot generate variants for an object without options.');

        $optionSet = [];
        $optionMap = [];

        foreach ($product->getOptions() as $key => $option) {
            $staticOptionValue = null;
            if (is_array($staticOptions) && array_key_exists($option->getCode(), $staticOptions) && null !== $staticOptions[$option->getCode()]) {
                $staticOptionValue = $staticOptions[$option->getCode()];
            }

            foreach ($option->getValues() as $value) {
                // If the option value is static, we skip the generation of the other variants.
                if (null === $staticOptionValue || $staticOptionValue === $value->getCode()) {
                    $optionSet[$key][] = $value->getCode();
                    $optionMap[$value->getCode()] = $value;
                }
            }
        }

        if (empty($optionSet)) {
            throw new VariantWithNoOptionsValuesException();
        }

        $permutations = $this->setBuilder->build($optionSet);

        foreach ($permutations as $permutation) {
            $variant = $this->createVariant($product, $optionMap, $permutation);

            if (!$this->variantsParityChecker->checkParity($variant, $product)) {
                $product->addVariant($variant);
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param array $optionMap
     * @param string|string[] $permutation
     * @return ProductVariantInterface
     */
    private function createVariant(ProductInterface $product, array $optionMap, $permutation): ProductVariantInterface
    {
        $variant = $this->productVariantFactory->createForProduct($product);
        $this->addOptionValue($variant, $optionMap, $permutation);

        return $variant;
    }

    /**
     * @param ProductVariantInterface $variant
     * @param array $optionMap
     * @param string|string[] $permutation
     */
    private function addOptionValue(ProductVariantInterface $variant, array $optionMap, $permutation): void
    {
        if (!is_array($permutation)) {
            $variant->addOptionValue($optionMap[$permutation]);

            return;
        }

        foreach ($permutation as $code) {
            $variant->addOptionValue($optionMap[$code]);
        }
    }
}
