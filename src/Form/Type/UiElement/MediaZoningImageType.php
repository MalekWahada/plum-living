<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaZoningImageType extends AbstractType
{
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // base64 image
        $this->addImage($builder, 'image', ['row-class' => 'slim-zoning-master']);

        // slider
        $builder->add('zone', CollectionType::class, [
            'label' => false,
            'entry_type' => MediaZoningElementType::class,
            'button_add_label' => 'app.form.media.slider.add_element',
            'attr' => [
                'class' => 'slim_zoning_elements',
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);
    }
}
