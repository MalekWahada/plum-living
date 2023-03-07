<?php

namespace App\Menu;

use Knp\Menu\Util\MenuManipulator;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    private MenuManipulator $menuManipulator;

    public function __construct(MenuManipulator $menuManipulator)
    {
        $this->menuManipulator = $menuManipulator;
    }

    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $factory = $event->getFactory();

        /**
         * Configurations
         */
        $catalogMenu = $menu->getChild("catalog");

        // add product groups to configuration submenu
        $productGroup = $factory->createItem('product_group', ['route' => 'app_admin_product_group_index'])
            ->setLabel('app.ui.product_groups')
            ->setLabelAttribute('icon', 'object group outline');
        $catalogMenu->addChild($productGroup);
        $this->menuManipulator->moveToPosition($productGroup, 2);

        // add product ikea to configuration submenu
        $catalogMenu
            ->addChild('product-ikea', ['route' => 'app_admin_product_ikea_index'])
            ->setLabel('app.ui.product_ikea')
            ->setLabelAttribute('icon', 'file alternate outline');

        /**
         * Facades
         */
        $facadeMenu = $menu
            ->addChild('facade')
            ->setLabel('app.facade.title')
        ;

        // add combination menu to facade submenu
        $facadeCombination = $factory->createItem('facade_combination', ['route' => 'app_admin_combination_index'])
            ->setLabel('app.combination.title')
            ->setLabelAttribute('icon', 'th');
        $facadeMenu->addChild($facadeCombination);

        /**
         * Configurations
         */
        $configMenu = $menu->getChild("configuration");

        // add delivery date calculation config to configuration submenu
        $deliveryDateCalculation = $factory->createItem('delivery_date_calculation', ['route' => 'app_admin_delivery_date_calculation_config_index'])
            ->setLabel('app.ui.delivery_date_calculation_configs')
            ->setLabelAttribute('icon', 'fast forward');
        $configMenu->addChild($deliveryDateCalculation);
        $this->menuManipulator->moveToPosition($deliveryDateCalculation, 8);
    }
}
