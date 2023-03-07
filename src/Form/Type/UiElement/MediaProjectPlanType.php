<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class MediaProjectPlanType extends AbstractType
{
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // slider
        $builder->add('slider', CollectionType::class, [
            'label' => false,
            'entry_type' => MediaSliderImageType::class,
            'button_add_label' => 'app.form.media.slider.add_element',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);

        // pdf
        $builder->add('file', FileType::class, [
                'label' => 'app.form.project_plan.label',
                'required' => true,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'app.constraints.project_plan',
                    ])
                ]
            ]);
    }
}
