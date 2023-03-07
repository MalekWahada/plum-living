<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use App\Entity\Product\ProductOptionValueImage;
use Sylius\Bundle\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductOptionValueImageType extends ImageType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices' => ProductOptionValueImage::ALLOWED_PRODUCT_OPTION_VALUE_IMAGE_TYPES,
                'choice_label' => fn (string $value): string => 'app.ui.product_option_value_image.type_' . $value,
                'required' => true,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_option_value_image';
    }
}
