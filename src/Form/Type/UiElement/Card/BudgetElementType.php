<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Card;

use App\Form\Type\UiElement\Traits\ImageElement;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BudgetElementType extends AbstractType
{
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // base64 image
        $this->addImage($builder, 'image');

        $builder->add('title', TextType::class, [
            'label' => 'app.form.card_element.title',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('description', TextType::class, [
            'label' => 'app.form.card_element.description',
        ]);

        $builder->add('price', MoneyType::class, [
            'label' => 'app.form.card_element.price',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }
}
