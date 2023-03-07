<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Card\CardElementType;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class CardTotalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cardMainTitle', TextType::class, [
            'label' => 'app.form.card_total.main_title',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
        $builder->add('cardTotalTitle', TextType::class, [
            'label' => 'app.form.card_total.total',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
        $builder->add('cardTotalToDisplay', MoneyType::class, [
            'label' => 'app.form.card_total.total_to_display',
            'required' => false,
        ]);
        $builder->add('cardTotalDetails', TextType::class, [
            'label' => 'app.form.card_total.total_details',
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ]);
        $builder->add('cardElements', CollectionType::class, [
            'label' => 'app.form.card_total.label',
            'entry_type' => CardElementType::class,
            'button_add_label' => 'app.form.card_total.add_button_label',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);
    }
}
