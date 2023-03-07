<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\ProductIkea\ProductIkeaAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaShoppinglistProductIkeaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ikea', ProductIkeaAutocompleteType::class, [
                'label' => false,
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'app.form.media.shoppinglist.quantity',
                'html5' => true,
            ]);
    }
}
