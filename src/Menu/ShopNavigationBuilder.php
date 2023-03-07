<?php

declare(strict_types=1);

namespace App\Menu;

use App\Provider\CMS\Chip\ChipProvider;
use App\Provider\CMS\PageCode\PageCodeProvider;
use App\Provider\CMS\PageContent\PageContentProvider;
use App\Provider\CMS\ProjectPiece\ProjectPieceProvider;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

final class ShopNavigationBuilder
{
    private const LINK_INSTALLATION_MANUAL = 'https://www.installation.plum-kitchen.com/';

    private FactoryInterface $factory;
    private PageContentProvider $pageContentProvider;
    private ChipProvider $chipProvider;

    public function __construct(
        FactoryInterface $factory,
        PageContentProvider $pageContentProvider,
        ChipProvider $chipProvider
    ) {
        $this->factory = $factory;
        $this->pageContentProvider = $pageContentProvider;
        $this->chipProvider = $chipProvider;
    }

    public function createShopNavigationMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('shop_nav');
        $menu->setLabel('app.menu.shop.nav.header');

        // concept nav
        $conceptMenuItem = $menu
            ->addChild('concept')
            ->setLabel('app.menu.shop.nav.concept.label')
            ->setAttribute('class', 'plum-nav__unlink');
        $conceptMenuItem
            ->addChild('functionality', $this->getRoute(PageCodeProvider::PAGE_CONCEPT_CODE))
            ->setLabel('app.menu.shop.nav.concept.functionality');
        $conceptMenuItem
            ->addChild('design_finish', $this->getRoute(PageCodeProvider::PAGE_DESIGN_FINISH_CODE))
            ->setLabel('app.menu.shop.nav.concept.design_finish');
        $conceptMenuItem
            ->addChild('faq', [
                'route' => 'app_faq_index'
            ])
            ->setLabel('app.menu.shop.nav.concept.faq');
        // projects navs
        $projectsMenuItem = $menu
            ->addChild('projects_and_budgets', [
                'route' => 'project_index'
            ])
            ->setLabel('app.menu.shop.nav.projects_and_budgets.label');

        $projectsMenuItem
            ->addChild('kitchen', [
                'route' => 'project_index',
                'routeParameters' => ['piece' => ProjectPieceProvider::PIECE_TYPE_KITCHEN]
            ])
            ->setLabel('app.menu.shop.nav.projects_and_budgets.kitchen');
        $projectsMenuItem
            ->addChild('rooms_and_dressing', [
                'route' => 'project_index',
                'routeParameters' => ['piece' => ProjectPieceProvider::PIECE_TYPE_ROOM]
            ])
            ->setLabel('app.menu.shop.nav.projects_and_budgets.rooms_and_dressing');
        $projectsMenuItem
            ->addChild('bathroom', [
                'route' => 'project_index',
                'routeParameters' => ['piece' => ProjectPieceProvider::PIECE_TYPE_BATHROOM]
            ])
            ->setLabel('app.menu.shop.nav.projects_and_budgets.bathroom');
        $projectsMenuItem
            ->addChild('others', [
                'route' => 'project_index',
                'routeParameters' => ['piece' => ProjectPieceProvider::PIECE_TYPE_OTHER]
            ])
            ->setLabel('app.menu.shop.nav.projects_and_budgets.others');

        // inspiration (media platform)
        $menu
            ->addChild('inspiration', $this->getRoute(PageCodeProvider::PAGE_MEDIA_HOME_CODE))
            ->setLabel('app.menu.shop.nav.inspiration_and_design.label');

        // services navs
        $servicesMenuItem = $menu->addChild('services')
            ->setLabel('app.menu.shop.nav.services.label')
            ->setAttribute('class', 'plum-nav__unlink');

        $servicesMenuItem
            ->addChild('styler_home', [
                'route' => 'app_plum_styler_index'
            ])
            ->setLabel('app.menu.shop.nav.services.styler_home');
        $servicesMenuItem
            ->addChild('conception_home', $this->getRoute(PageCodeProvider::PAGE_CONCEPTION_HOME_CODE))
            ->setLabel('app.menu.shop.nav.services.conception_home');
        $servicesMenuItem
            ->addChild('quotation_home', $this->getRoute(PageCodeProvider::PAGE_QUOTATION_HOME_CODE))
            ->setLabel('app.menu.shop.nav.services.quotation_home');
        $servicesMenuItem
            ->addChild('find_installer', $this->getRoute(PageCodeProvider::PAGE_FIND_INSTALLER_CODE))
            ->setLabel('app.menu.shop.nav.services.find_installer');

        // e_shop navs
        $eShopMenuItem = $menu->addChild('e_shop')
            ->setLabel('app.menu.shop.nav.e_shop.label')
            ->setAttribute('class', 'plum-nav__unlink');

        $eShopMenuItem
            ->addChild('sample_facade', [
                'route' => 'listing_sample_facade'
            ])
            ->setLabel('app.menu.shop.nav.e_shop.sample.facade');
        $eShopMenuItem
            ->addChild('sample_paint_mural', [
                'route' => 'listing_sample_paint_mural'
            ])
            ->setLabel('app.menu.shop.nav.e_shop.sample.paint_mural');
        $eShopMenuItem
            ->addChild('facade_method', [
                'route' => 'facade_get_designs',
                'routeParameters' => ['facadeTypeCode' => 'metod']
            ])
            ->setLabel('app.menu.shop.nav.e_shop.facade_method');
        $eShopMenuItem
            ->addChild('facade_pax', [
                'route' => 'facade_get_designs',
                'routeParameters' => ['facadeTypeCode' => 'pax']
            ])
            ->setLabel('app.menu.shop.nav.e_shop.facade_pax');
        $eShopMenuItem
            ->addChild('tap', [
                'route' => 'listing_tap'
            ])
            ->setLabel('app.menu.shop.nav.e_shop.taps');
        $eShopMenuItem
            ->addChild('accessory', [
                'route' => 'listing_accessoires'
            ])
            ->setLabel('app.menu.shop.nav.e_shop.accessory');
        $eShopMenuItem
            ->addChild('paint_mural', [
                'route' => 'listing_paint'
            ])
            ->setLabel('app.menu.shop.nav.e_shop.paints');


        // login link - mobile only
        $menu
            ->addChild('user-account', [
                'route' => 'sylius_shop_account_profile_update'
            ])
            ->setAttribute('class', '@lg:u-hidden menu-link-login u-padding-t-4')
            ->setLabel('app.menu.shop.nav.account.login');

        // quotation navs
        $menu
            ->addChild('quotation', $this->getRoute(PageCodeProvider::PAGE_QUOTATION_HOME_CODE))
            ->setAttribute('class', 'quotation-home-button button button--inversed c-near-black @lg:u-hidden')
            ->setLabel('app.menu.shop.nav.quotation');
        $menu
            ->addChild('styler_home', [
                'route' => 'app_plum_styler_index'
            ])
            ->setAttribute('class', 'styler-home-button-mobile button button--inversed c-near-black bg-pink-light @lg:u-hidden')
            ->setLabel('app.menu.shop.nav.styler_button');


        return $menu;
    }

    public function createShopAsideHeaderMenu(): ItemInterface
    {
        $asideHeaderMenu = $this->factory->createItem('shop_aside_header');
        $asideHeaderMenu->setLabel('app.menu.shop.aside_header.label');

        $asideHeaderMenu
            ->addChild('about', $this->getRoute(PageCodeProvider::PAGE_ABOUT_CODE))
            ->setLabel('app.menu.shop.aside_header.about');
        $asideHeaderMenu
            ->addChild('manufacturing', $this->getRoute(PageCodeProvider::PAGE_SHIPPING_MANUFACTURING_CODE))
            ->setLabel('app.menu.shop.aside_header.manufacturing');
        $asideHeaderMenu
            ->addChild('delivery', $this->getRoute(PageCodeProvider::PAGE_SHIPPING_DELIVERY_CODE))
            ->setLabel('app.menu.shop.aside_header.delivery');
        $asideHeaderMenu
            ->addChild('faq', [
                'route' => 'app_faq_index'
            ])
            ->setLabel('app.menu.shop.aside_header.faq');

        return $asideHeaderMenu;
    }

    public function createShopFooterMenu(): ItemInterface
    {
        $footerMenu = $this->factory->createItem('footer');
        $footerMenu
            ->addChild('concept', $this->getRoute(PageCodeProvider::PAGE_CONCEPT_CODE))
            ->setLabel('app.menu.shop.nav.concept.label');
        $footerMenu
            ->addChild('design_and_finish', $this->getRoute(PageCodeProvider::PAGE_DESIGN_FINISH_CODE))
            ->setLabel('app.menu.shop.nav.concept.design_finish');
        $footerMenu
            ->addChild('manufacturing', $this->getRoute(PageCodeProvider::PAGE_SHIPPING_MANUFACTURING_CODE))
            ->setLabel('app.menu.shop.aside_header.manufacturing');
        $footerMenu
            ->addChild('delivery', $this->getRoute(PageCodeProvider::PAGE_SHIPPING_DELIVERY_CODE))
            ->setLabel('app.menu.shop.aside_header.delivery');
        $footerMenu
            ->addChild('installationManual', [
                'uri' => self::LINK_INSTALLATION_MANUAL
            ])
            ->setLabel('app.menu.shop.footer.installation_manual');
        $footerMenu
            ->addChild('find_installer', $this->getRoute(PageCodeProvider::PAGE_FIND_INSTALLER_CODE))
            ->setLabel('app.menu.shop.nav.services.find_installer');
        $footerMenu
            ->addChild('about', $this->getRoute(PageCodeProvider::PAGE_ABOUT_CODE))
            ->setLabel('app.menu.shop.aside_header.about');
        $footerMenu
            ->addChild('terraClub', $this->getRoute(PageCodeProvider::PAGE_TERRA_CLUB_CODE))
            ->setLabel('app.menu.shop.footer.terra_club');
        $footerMenu
            ->addChild('faq', [
                'route' => 'app_faq_index'
            ])
            ->setLabel('app.menu.shop.aside_header.faq');
        $footerMenu
            ->addChild('cgv', $this->getRoute(PageCodeProvider::PAGE_CGV_CODE))
            ->setLabel('app.menu.shop.footer.cgv');
        $footerMenu
            ->addChild('confidentiality', $this->getRoute(PageCodeProvider::PAGE_CONFIDENTIALITY_CODE))
            ->setLabel('app.menu.shop.footer.confidentiality');
        $footerMenu
            ->addChild('legal_mentions', $this->getRoute(PageCodeProvider::PAGE_LEGAL_MENTIONS_CODE))
            ->setLabel('app.menu.shop.footer.legal_mentions');

        return $footerMenu;
    }

    private function getRoute(string $pageCode): array
    {
        $slug = $this->pageContentProvider->getSlug($pageCode);
        if (null !== $slug) {
            return [
                'route' => 'monsieurbiz_cms_page_show',
                'routeParameters' => ['slug' => $slug]
            ];
        }

        return [];
    }
}
