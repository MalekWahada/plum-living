<?php

declare(strict_types=1);

namespace App\Provider\Product;

use App\Provider\Translation\TranslationLocaleProvider;
use Sylius\Component\Attribute\AttributeType\SelectAttributeType;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ProductAttributeProvider
{
    private RepositoryInterface $productAttributeRepository;
    private AttributeFactoryInterface $productAttributeFactory;
    private TranslationLocaleProvider $localeProvider;

    public function __construct(RepositoryInterface $productAttributeRepository, AttributeFactoryInterface $productAttributeFactory, TranslationLocaleProvider $localeProvider)
    {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeFactory = $productAttributeFactory;
        $this->localeProvider = $localeProvider;
    }

    /**
     * Get or create attribute.
     * @param string $code
     * @param string $type
     * @return ProductAttributeInterface
     */
    public function provideAttribute(string $code, string $type = SelectAttributeType::TYPE): ProductAttributeInterface
    {
        $attribute = $this->productAttributeRepository->findOneBy(['code' => $code]);

        if (null === $attribute) {
            $attribute = $this->productAttributeFactory->createTyped($type);
            $attribute->setCode($code);
            $attribute->setName(ucfirst($code));

            if ($type === SelectAttributeType::TYPE) {
                $attribute->setConfiguration([
                    'choices' => [],
                    'multiple' => false,
                    'min' => null,
                    'max' => null,
                ]);
            }
        }

        return $attribute;
    }

    /**
     * Create attribute choice for all locales available
     * @param ProductAttributeInterface $productAttribute
     * @param string $key
     * @param string $name
     */
    public function createAttributeChoiceIfNotExist(ProductAttributeInterface $productAttribute, string $key, string $name): void
    {
        $configuration = $productAttribute->getConfiguration();

        if (!isset($configuration['choices'])) {
            $configuration['choices'] = [];
        }

        if (!isset($configuration['choices'][$key])) {
            $configuration['choices'][$key] = [];
        }

        foreach ($this->localeProvider->getDefinedLocalesCodesOrDefault() as $locale) {
            if (!isset($configuration['choices'][$key][$locale])) {
                $configuration['choices'][$key][$locale] = $name;
            }
        }

        $productAttribute->setConfiguration($configuration);
    }
}
