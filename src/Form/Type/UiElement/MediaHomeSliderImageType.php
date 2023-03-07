<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType as FormTextType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaHomeSliderImageType extends AbstractType
{
    use ImageElement;
    use LinkElement;
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image_title', FormTextType::class, [
                'label' => "Titre de l'article mis en avant",
            ])
            ->add('image_category', FormTextType::class, [
                'label' => "Catégorie de l'article mis en avant",
            ])
            ->add('image_description', FormTextType::class, [
                'required' => false,
                'label' => "Description courte de l'article mis en avant",
            ])
            ->add('text_color', ChoiceType::class, [
                'label' => 'app.form.media.default.text_color.title',
                'choices' => [
                    'app.form.media.default.text_color.white' => 'c-true-white',
                    'app.form.media.default.text_color.black' => 'c-true-black',
                ],
            ])
        ;

        // link
        $this->addLink($builder, 'link');

        // base64 image
        $this->addImage($builder, 'image');
    }
}
