<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\ImageStepElement\ImageStepElementType;
use App\Provider\CMS\ImagesSteps\ImagesStepsProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

final class ImagesStepsType extends AbstractType
{
    private ImagesStepsProvider $imagesStepsProvider;

    public function __construct(ImagesStepsProvider $imagesStepsProvider)
    {
        $this->imagesStepsProvider = $imagesStepsProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'label' => 'app.form.images_steps.type',
            'choices' => $this->imagesStepsProvider->getImagesStepsTypes(),
            'choice_label' => fn (string $label): string => 'app.ui.cms.images_steps.type_' . $label,
        ]);
        $builder->add('imagesSteps', CollectionType::class, [
            'label' => 'app.form.images_steps.label',
            'entry_type' => ImageStepElementType::class,
            'button_add_label' => 'app.form.images_steps.add_button_label',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);
    }
}
