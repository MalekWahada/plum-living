<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use Sylius\Bundle\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class CombinationImageType extends ImageType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('type')
            ->add('file', FileType::class, [
                'label' => false,
                'required'  => true
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_combination_image';
    }
}
