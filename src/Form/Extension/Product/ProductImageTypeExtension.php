<?php

declare(strict_types=1);

namespace App\Form\Extension\Product;

use Sylius\Bundle\CoreBundle\Form\Type\Product\ProductImageType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductImageTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('position', IntegerType::class, [
            'label' => 'app.form.product.position',
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductImageType::class];
    }
}
