<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MediaTextOnlyType extends AbstractType
{
    use ColorBackgroundElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // background color
        $this->addBgColor($builder);

        // description
        $builder->add('description', WysiwygType::class, [
            'label' => 'app.form.media.default.description',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }
}
