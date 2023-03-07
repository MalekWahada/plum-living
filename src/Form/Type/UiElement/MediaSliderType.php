<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Card\MediaCardType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MediaSliderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // title
        $builder->add('title', TextType::class, [
            'label' => 'app.form.media.default.title',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        // slider
        $builder->add('slider', CollectionType::class, [
            'label' => false,
            'entry_type' => MediaCardType::class,
            'button_add_label' => 'app.form.media.slider.add_element',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);
    }
}
