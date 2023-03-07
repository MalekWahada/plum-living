<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class ProductVariantAutocompleteType extends AbstractType
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product_variant', TextType::class, [
                'label' => 'sylius.ui.product_variant',
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-route' => $this->router->generate('app_admin_ajax_product_variant_autocomplete', [
                        'locale' => 'fr'
                    ]),
                ],
            ])
        ;
    }
}
