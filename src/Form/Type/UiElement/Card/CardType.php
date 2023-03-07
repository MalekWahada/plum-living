<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Card;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\UiElement\ImageType;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', WysiwygType::class, [
            'label' => 'app.form.card.content',
            'constraints' => [
                new Assert\NotBlank([]),
            ],
        ]);

        $builder->add('link', TextType::class, [
            'label' => 'app.form.card.link',
            'required' => false,
        ]);

        $builder->add('linkLabel', TextType::class, [
            'label' => 'app.form.card.link_label',
            'required' => false,
        ]);

        $builder->add('withIcon', CheckboxType::class, [
            'label' => 'app.form.card.with_icon',
            'required' => false,
        ]);

        $builder->add('image', ImageType::class, [
            'label' => false,
        ]);
    }
}
