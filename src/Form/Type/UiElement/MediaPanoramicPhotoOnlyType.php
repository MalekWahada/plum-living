<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaPanoramicPhotoOnlyType extends AbstractType
{
    use ColorBackgroundElement;
    use ImageElement;
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // template margin (margin OR immersive)
        $builder->add('margin', ChoiceType::class, [
            'label' => 'app.form.media.two_hybrid_columns.margin',
            'choices' => [
                'app.form.media.two_hybrid_columns.margin_marge' => 'margin',
                'app.form.media.two_hybrid_columns.margin_immersive' => 'immersive',
            ],
        ]);

        // base64 image
        $this->addImage($builder, 'image');

        // legende photo
        $builder->add('caption', TextType::class, [
            'label' => 'app.form.media.default.caption',
            'required' => false,
        ]);
    }
}
