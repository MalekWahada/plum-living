<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaSliderImageType extends AbstractType
{
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // base64 image
        $this->addImage($builder, 'image');
    }
}
