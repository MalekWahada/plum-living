<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;

class MediaCitationType extends AbstractType
{
    use ColorBackgroundElement;
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // background color
        $this->addBgColor($builder);

        // template orientation (left OR right)
        $builder->add('orientation', ChoiceType::class, [
            'label' => 'app.form.media.two_hybrid_columns.orientation',
            'choices' => [
                'app.form.media.two_hybrid_columns.orientation_left' => 'left',
                'app.form.media.two_hybrid_columns.orientation_right' => 'right',
            ],
        ]);

        // citation
        $builder->add('citation', TextType::class, [
            'label' => 'app.form.media.default.citation',
            'constraints' => [
                new Assert\NotBlank(),
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
