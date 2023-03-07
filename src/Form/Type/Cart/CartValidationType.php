<?php

declare(strict_types=1);

namespace App\Form\Type\Cart;

use App\Entity\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class CartValidationType extends AbstractType
{
    public const CHOICES_TRANSLATION_PREFIX = 'app.ui.checkout.confirmation_modal.target_room.options.';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('validateItemsCount', CheckboxType::class, [
            'label' => 'app.ui.checkout.confirmation_modal.product_count.checkbox_label',
            'mapped' => false,
        ]);
        $builder->add('hasOriginalOrder', ChoiceType::class, [
            'choices' => [
                'sylius.ui.yes_label' => true,
                'sylius.ui.no_label' => false,
            ],
            'multiple' => false,
            'expanded' => true,
            'mapped' => false,
            'data' => 1,
        ]);
        $builder->add('originalOrderNumber', TextType::class, [
            'label' => false,
        ]);
        $builder->add('targeted_room', ChoiceType::class, [
            'label' => false,
            'choices' => array_combine(
                array_map(fn ($item) => self::CHOICES_TRANSLATION_PREFIX . $item, Order::targetedRoomsList()),
                Order::targetedRoomsList()
            ),
            'choice_translation_domain' => 'messages',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_cart_validation';
    }
}
