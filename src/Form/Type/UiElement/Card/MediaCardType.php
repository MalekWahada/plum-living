<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Card;

use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaCardType extends AbstractType
{
    use ImageElement;
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // order
        $builder->add('order', NumberType::class, [
            'label' => 'app.form.media.slider.order',
            'html5' => true,
        ]);

        // link
        $this->addLink($builder, 'link');

        // base64 image
        $this->addImage($builder, 'image');
    }
}
