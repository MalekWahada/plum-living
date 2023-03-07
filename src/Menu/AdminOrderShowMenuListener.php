<?php

declare(strict_types=1);

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminOrderShowMenuListener
{
    public function removeCancelAction(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu->removeChild("cancel");
    }
}
