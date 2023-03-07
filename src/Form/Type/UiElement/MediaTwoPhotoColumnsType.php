<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaTwoPhotoColumnsType extends AbstractType
{
    use ColorBackgroundElement;
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // left base64 image
        $this->addImage($builder, 'image_left');

        // legende photo
        $builder->add('caption1', TextType::class, [
            'label' => 'app.form.media.default.caption',
            'required' => false,
        ]);

        // right base64 image
        $this->addImage($builder, 'image_right');

        // legende photo
        $builder->add('caption2', TextType::class, [
            'label' => 'app.form.media.default.caption',
            'required' => false,
        ]);
    }
}
