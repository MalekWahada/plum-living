<?php

declare(strict_types=1);

namespace App\Provider\Product;

use App\Provider\Translation\TranslationLocaleProvider;
use App\Repository\Product\ProductAttributeValueRepositoryInterface;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ProductAttributeValueProvider
{
    private ProductAttributeValueRepositoryInterface $productAttributeValueRepository;
    private FactoryInterface $productAttributeValueFactory;
    private TranslationLocaleProvider $localeProvider;

    public function __construct(ProductAttributeValueRepositoryInterface $productAttributeValueRepository, FactoryInterface $productAttributeValueFactory, TranslationLocaleProvider $localeProvider)
    {
        $this->productAttributeValueRepository = $productAttributeValueRepository;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->localeProvider = $localeProvider;
    }

    /**
     * Get all attribute values for a product for all locales available.
     * @param ProductInterface $product
     * @param AttributeInterface $attribute
     * @return array|ProductAttributeValueInterface[]
     */
    public function provideAttributeValues(ProductInterface $product, AttributeInterface $attribute): array
    {
        $attributeValues = $this->productAttributeValueRepository->findByProductAndAttribute($product, $attribute);
        $attributeValuesLocalized = [];
        foreach ($attributeValues as $attributeValue) {
            $attributeValuesLocalized[$attributeValue->getLocaleCode()] = $attributeValue;
        }

        foreach ($this->localeProvider->getDefinedLocalesCodesOrDefault() as $locale) {
            if (!isset($attributeValuesLocalized[$locale])) {
                $attributeValuesLocalized[$locale] = $this->createNew($product, $attribute, $locale);
            }
        }
        return $attributeValuesLocalized;
    }

    private function createNew(ProductInterface $product, AttributeInterface $attribute, string $locale): ProductAttributeValueInterface
    {
        /** @var ProductAttributeValueInterface $attributeValue */
        $attributeValue = $this->productAttributeValueFactory->createNew();
        $attributeValue->setAttribute($attribute);
        $attributeValue->setProduct($product);
        $product->addAttribute($attributeValue);
        $attributeValue->setLocaleCode($locale);

        return $attributeValue;
    }
}
