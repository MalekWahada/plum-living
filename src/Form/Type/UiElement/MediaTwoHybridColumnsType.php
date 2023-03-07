<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MediaTwoHybridColumnsType extends AbstractType
{
    use ColorBackgroundElement;
    use ImageElement;
    use LinkElement;

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

        // template margin (margin OR immersive)
        $builder->add('margin', ChoiceType::class, [
            'label' => 'app.form.media.two_hybrid_columns.margin',
            'choices' => [
                'app.form.media.two_hybrid_columns.margin_marge' => 'margin',
                'app.form.media.two_hybrid_columns.margin_immersive' => 'immersive',
            ],
        ]);

        // title
        $builder->add('title', TextType::class, [
            'label' => 'app.form.media.default.title',
            'required' => false,
        ]);

        // description
        $builder->add('description', WysiwygType::class, [
            'label' => 'app.form.media.default.description',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        // link
        $this->addLink($builder, 'link');

        // base64 image
        $this->addImage($builder, 'image');

        // legende photo
        $builder->add('caption', TextType::class, [
            'label' => 'app.form.media.default.caption',
            'required' => false,
        ]);
    }
}
