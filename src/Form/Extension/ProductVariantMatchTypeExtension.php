<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Form\Type\Product\ProductOptionValueChoiceHiddenType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantMatchType;
use Sylius\Component\Product\Model\ProductInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantMatchTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'entries' => function (Options $options) {
                    /** @var ProductInterface $product */
                    $product = $options['product'];

                    return $product->getOptions();
                },
                'entry_type' => function (Options $options) {
                    return function (ProductOption $productOption) use ($options) {
                        if ($this->isHiddenFormField($productOption, $options['selectedOptions'] ?? null)) {
                            return ProductOptionValueChoiceHiddenType::class;
                        }
                        return ProductOptionValueChoiceType::class;
                    };
                },
                'entry_name' => function (ProductOption $productOption) {
                    return $productOption->getCode();
                },
                'entry_options' => function (Options $options) {
                    return function (ProductOption $productOption) use ($options) {
                        $entryOptions = ['label' => $productOption->getName()];
                        if ($this->isHiddenFormField($productOption, $options['selectedOptions'] ?? null)) {
                            $productOptionValueObj = null;
                            $optionPayloadCode = $productOption->getCode().'Code';
                            if (isset($options['selectedOptions'][$optionPayloadCode])) {
                                $productOptionValueObj = $productOption->getValueByCode($options['selectedOptions'][$optionPayloadCode]);
                            }

                            if (null === $productOptionValueObj) {
                                throw new BadRequestHttpException('Product option value not found');
                            }

                            return $entryOptions[] = [
                                // Select the option value associated to the selected option
                                'data' => $productOptionValueObj,
                                'productOptionValue' => $productOptionValueObj
                            ];
                        }
                        return $entryOptions[] = [
                            'option' => $productOption,
                            'only_available_values' => true,
                            'product' => $options['product'],
                        ];
                    };
                },
            ])
            ->setRequired('product')
            ->setAllowedTypes('product', ProductInterface::class)
            ->setDefined([
                'selectedOptions',
                'productOptionValue'
            ])
            ->setAllowedTypes('selectedOptions', ['string[]', 'null'])
            ->setAllowedTypes('productOptionValue', ProductOptionValue::class)
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductVariantMatchType::class];
    }

    //check for a hidden field for a specific needs (Exp: hide options in Eshop Form add to cart)
    public function isHiddenFormField(ProductOption $productOption, ?array $selectedOption = null) :bool
    {
        if (null === $selectedOption) {
            return false;
        }

        $optionCode = $productOption->getCode();
        if ($optionCode === ProductOption::PRODUCT_OPTION_DESIGN
            ||
            $optionCode === ProductOption::PRODUCT_OPTION_FINISH
            ||
            ( //hide color field for finishes without color
                $optionCode === ProductOption::PRODUCT_OPTION_COLOR
                &&
                in_array($selectedOption['finishCode'], ProductOptionValue::FINISH_WITHOUT_SELECTED_COLORS, true)
            )
        ) {
            return true;
        }
        return false;
    }
}
