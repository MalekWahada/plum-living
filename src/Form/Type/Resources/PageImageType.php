<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use Sylius\Bundle\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class PageImageType extends ImageType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_page_image';
    }
}
