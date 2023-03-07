<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class OptionRecommendationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'app.form.recommendation.option.title',
            'data' => 'option'
        ]);
        $builder->add('description', WysiwygType::class, [
            'label' => 'app.form.recommendation.option.description',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
        $builder->add('image', FileType::class, [
            'label' => 'app.form.recommendation.option.image',
            'data_class' => null,
            'required' => true,
            'attr' => ['data-image' => 'true'], // To be able to manage display in form
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
    }
}
