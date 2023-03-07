<?php

declare(strict_types=1);

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class ProductFormMenuBuilderListener
{
    public function addProductCompleteInfo(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu
            ->addChild('complete-info')
            ->setAttribute('template', 'Admin/Product/product_complete_info.html.twig')
            ->setLabel('app.ui.admin.product.form_menu.complete_info.label')
        ;
    }
}
