<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\ColorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class ColorUIType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('color', ColorType::class, [
            'label' => 'app.form.color',
        ]);
    }
}
