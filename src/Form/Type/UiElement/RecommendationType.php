<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RecommendationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('subtitle', WysiwygType::class, [
            'label' => 'app.form.recommendation.subtitle',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('options', CollectionType::class, [
            'entry_type' => OptionRecommendationType::class,
            'button_add_label' => 'app.form.recommendation.option.add',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
            'label' => 'app.form.recommendation.option.list',
        ]);
    }
}
