<?php

declare(strict_types=1);

namespace App\Form\Type\Resources\Order;

use App\Entity\Product\Product;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use Sylius\Bundle\OrderBundle\Form\Type\CartItemType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddToCartTunnelShoppingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'cartItem',
            CartItemType::class,
            [
                'product' => $options['product'],
                'selectedOptions' => $options['selectedOptionsValues']
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefined([
                'product',
            ])
            ->setAllowedTypes('product', ProductInterface::class)
        ;
        $resolver
            ->setDefined([
                'selectedOptionsValues',
            ])
            ->setAllowedTypes('selectedOptionsValues', [])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_tunnel_add_to_cart';
    }
}
