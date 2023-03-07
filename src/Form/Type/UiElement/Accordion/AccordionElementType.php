<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Accordion;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AccordionElementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'required' => true,
            'label' => 'app.form.accordion.title',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
        $builder->add('detail', WysiwygType::class, [
            'required' => true,
            'label' => 'app.form.accordion.detail',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
    }
}
