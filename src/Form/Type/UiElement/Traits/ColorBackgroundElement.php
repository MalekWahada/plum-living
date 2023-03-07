<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Traits;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

trait ColorBackgroundElement
{
    public function addBgColor(FormBuilderInterface $builder): void
    {
        // label
        $builder->add('bg_color', ChoiceType::class, [
            'label' => 'app.form.media.default.bg_color.title',
            'required' => false,
            'choices' => [
                'app.form.media.default.bg_color.white' => 'bg-true-white',
                'app.form.media.default.bg_color.beige' => 'bg-beige',
                'app.form.media.default.bg_color.rose' => 'bg-pink-light',
                'app.form.media.default.bg_color.blue' => 'bg-bleu-pastel',
                'app.form.media.default.bg_color.green' => 'bg-vert-pastel',
            ]
        ]);
    }
}
