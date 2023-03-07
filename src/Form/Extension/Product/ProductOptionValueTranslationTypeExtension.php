<?php

declare(strict_types=1);

namespace App\Form\Extension\Product;

use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductOptionValueTranslationTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('description', TextType::class, [
            'label' => 'app.form.product_value.description',
            'required'=> false
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductOptionValueTranslationType::class];
    }
}
