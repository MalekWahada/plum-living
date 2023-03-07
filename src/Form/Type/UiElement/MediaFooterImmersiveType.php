<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MediaFooterImmersiveType extends AbstractType
{
    use ImageElement;
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // tag
        $builder->add('tag', TextType::class, [
            'label' => 'app.form.media.hero.tag',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        // title
        $builder->add('title', TextType::class, [
            'label' => 'app.form.media.default.title',
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
