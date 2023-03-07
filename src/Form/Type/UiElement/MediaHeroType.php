<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MediaHeroType extends AbstractType
{
    use ColorBackgroundElement;
    use ImageElement;
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // background color
        $this->addBgColor($builder);

        // title
        $builder->add('title', TextType::class, [
            'label' => 'app.form.media.default.title',
            'constraints' => [
                new Assert\NotBlank(),
            ],
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
    }
}
