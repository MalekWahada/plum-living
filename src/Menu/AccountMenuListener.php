<?php

declare(strict_types=1);

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AccountMenuListener
{
    public function updateAccountMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu->removeChild('dashboard');
        $menu->removeChild('change_password');

        $menu->getChild('address_book')->setLabel('app.ui.account.menu.address_book');
        $menu->getChild('order_history')->setLabel('app.ui.account.menu.order_history');
        $menu->getChild('personal_information')->setLabel('app.ui.account.menu.personal_information');

        $menu
            ->addChild('projects', ['route' => 'app_account_project_list',])
            ->setAttribute('type', 'link')
            ->setLabel('app.ui.account.menu.projects')
        ;
    }
}
