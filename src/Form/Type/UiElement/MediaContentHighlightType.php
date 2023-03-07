<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaContentHighlightType extends AbstractType
{
    use ImageElement;
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // titre photo
        $builder->add('title1', TextType::class, [
            'label' => 'Titre du premier bloc',
        ]);

        // desc photo
        $builder->add('description1', TextType::class, [
            'label' => 'Description courte du premier bloc',
            'required' => false,
        ]);

        // left base64 image
        $this->addImage($builder, 'image1');

        // link
        $this->addLink($builder, 'link');

        // titre photo
        $builder->add('title2', TextType::class, [
            'label' => 'Titre du deuxième bloc',
        ]);

        // desc photo
        $builder->add('description2', TextType::class, [
            'label' => 'Description courte du deuxième bloc',
            'required' => false,
        ]);

        // left base64 image
        $this->addImage($builder, 'image2');

        // link
        $this->addLink($builder, 'link2');
    }
}
