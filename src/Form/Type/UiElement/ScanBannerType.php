<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ScanBannerType extends AbstractType
{
    use ImageElement;
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // title
        $builder->add('title', TextType::class, [
            'label' => 'app.form.media.default.title',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        // title
        $builder->add('title_secondary', TextType::class, [
            'label' => 'partie verte du titre',
        ]);

        // description
        $builder->add('description', WysiwygType::class, [
            'label' => 'app.form.product_value.description',
            'required' => true
        ]);

        // link
        $this->addLink($builder, 'link');

        // link
        $this->addLink($builder, 'link_secondary');

        // base64 image
        $this->addImage($builder, 'image');
    }
}
