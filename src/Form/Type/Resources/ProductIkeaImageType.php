<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductIkeaImageType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', UrlType::class, [
                'label' => false,
                'required'  => true
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_ikea_image';
    }
}
