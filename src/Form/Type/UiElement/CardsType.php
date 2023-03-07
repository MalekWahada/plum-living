<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Card\CardType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CardsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cards', CollectionType::class, [
            'entry_type' => CardType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => [
                new Assert\NotBlank([]),
            ],
        ]);
    }
}
