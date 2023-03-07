<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\ImageStepElement;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ImageStepElementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('titleStep', TextType::class, [
            'label' => 'app.form.image_step_element.image_step_title',
        ]);
        $builder->add('imageStep', FileType::class, [
            'label' => 'app.form.image_step_element.image_step_image',
            'data_class' => null,
            'attr' => ['data-image' => 'true'], // To be able to manage display in form
        ]);
        $builder->add('descriptionStep', WysiwygType::class, [
            'label' => 'app.form.image_step_element.image_step_description',
        ]);
    }
}
