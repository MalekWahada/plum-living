<?php

declare(strict_types=1);

namespace App\Menu;

use Knp\Menu\Util\MenuManipulator;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminProductGroupFormMenuListener
{
    private MenuManipulator $menuManipulator;

    public function __construct(MenuManipulator $menuManipulator)
    {
        $this->menuManipulator = $menuManipulator;
    }

    public function addItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('groups')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/_groups.html.twig')
            ->setLabel('app.ui.product_groups')
        ;
        $this->menuManipulator->moveToPosition($menu->getChild('groups'), 2);
    }
}
