<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\ProductInfo;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', WysiwygType::class, [
            'required' => true,
            'label' => 'app.form.product_info.content',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }
}
