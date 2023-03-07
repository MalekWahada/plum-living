<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Product\Product;
use Sylius\Bundle\OrderBundle\Form\Type\CartItemType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantMatchType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CartItemTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('comment', TextType::class, [
            'required' => false,
            'label' => 'app.form.cart_item.comment',
            'attr' => [
                'placeholder' => 'app.form.cart_item.comment_placeholder'
            ]
        ]);

        if (isset($options['product']) && $options['product']->hasVariants() && !$options['product']->isSimple()) {
            $type =
                Product::VARIANT_SELECTION_CHOICE === $options['product']->getVariantSelectionMethod()
                    ? ProductVariantChoiceType::class
                    : ProductVariantMatchType::class;
            $allowedVariants = $options['product']->getVariants();
            $builder->add('variant', $type, [
                'product' => $options['product'],
                'selectedOptions' => $options['selectedOptions'] ?? null,
                'attr' => [
                    'hasToBeDisplayed' => count($allowedVariants) > 1,
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'selectedOptions',
            ])
            ->setAllowedTypes('selectedOptions', 'string[]');
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartItemType::class];
    }
}
