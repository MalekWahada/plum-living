<?php

declare(strict_types=1);

namespace App\Menu;

use App\Entity\Product\ProductGroup;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

final class AdminProductGroupShowMenuBuilder
{
    private FactoryInterface $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createMenu(array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!array_key_exists('product_group', $options) || !$options['product_group'] instanceof ProductGroup) {
            return $menu;
        }

        $menu
            ->addChild('update', [
                'route' => 'app_admin_product_group_update',
                'routeParameters' => [
                    'id' => $options['product_group']->getId(),
                ],
            ])
            ->setLabelAttribute('icon', 'edit')
            ->setLabel('sylius.ui.edit')
            ->setCurrent(true)
        ;

        return $menu;
    }
}
