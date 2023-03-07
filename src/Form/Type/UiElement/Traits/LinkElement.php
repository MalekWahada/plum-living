<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Traits;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\AlignmentType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType as FormTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;

trait LinkElement
{
    public function addLink(FormBuilderInterface $builder, string $baseName = 'link'): void
    {
        // label
        $builder->add($baseName . '_label', FormTextType::class, [
            'label' => 'app.form.media.default.link_label',
            'required' => false,
            'constraints' => [
                new Length(['max' => 100]),
            ],
        ]);

        // url
        $builder->add($baseName . '_url', FormTextType::class, [
            'label' => 'app.form.media.default.link_url',
            'required' => false,
//            'constraints' => [
//                new Url([]),
//            ],
        ]);

        // target blank
        $builder->add($baseName . '_target', CheckboxType::class, [
            'label' => 'app.form.media.default.link_target',
            'value' => '_blank',
            'required' => false,
        ]);
    }
}
