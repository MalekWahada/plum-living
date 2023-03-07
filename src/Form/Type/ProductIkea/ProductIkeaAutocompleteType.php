<?php

declare(strict_types=1);

namespace App\Form\Type\ProductIkea;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class ProductIkeaAutocompleteType extends AbstractType
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', TextType::class, [
                'label' => 'app.form.media.shoppinglist.product',
                'attr' => [
                    'class' => 'js-autocomplete',
                    'data-route' => $this->router->generate('app_admin_ajax_product_ikea_autocomplete', [
                        'locale' => 'fr'
                    ]),
                ],
            ])
        ;
    }
}
