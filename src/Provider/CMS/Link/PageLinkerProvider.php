<?php

declare(strict_types=1);

namespace App\Provider\CMS\Link;

use App\Dto\PageLinker\PageLinkerDto;
use App\Entity\Page\Page;
use App\Provider\CMS\Page\PageProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageLinkerProvider
{
    private const INTERNAL_ROUTES_TO_LINK = [
        'app_plum_scanner_get_designs',
    ];

    private PageProvider $pageProvider;
    private TranslatorInterface $translator;

    public function __construct(
        PageProvider $pageProvider,
        TranslatorInterface $translator
    ) {
        $this->pageProvider = $pageProvider;
        $this->translator = $translator;
    }

    /**
     * @return PageLinkerDto[]|array
     */
    public function getPageLinkers(): array
    {
        return [
            ...$this->getCMSPageLinkers(),
            ...$this->getRoutePageLinkers(),
        ];
    }

    public function getPageLinkerTitle(array $linkers, string $code): string
    {
        /** @var PageLinkerDto $linker */
        foreach ($linkers as $linker) {
            if ($linker->getCode() === $code) {
                return $linker->getTitle();
            }
        }
        return '';
    }

    public function isInternalRouterLinker(string $linkerCode): bool
    {
        return in_array($linkerCode, self::INTERNAL_ROUTES_TO_LINK);
    }

    /**
     * @return PageLinkerDto[]|array
     */
    private function getCMSPageLinkers(): array
    {
        $pageTypes = [
            Page::PAGE_TYPE_DEFAULT,
            Page::PAGE_TYPE_PROJECT,
            Page::PAGE_TYPE_ARTICLE,
            Page::PAGE_TYPE_FRAME,
        ];

        $pages = $this->pageProvider->getPagesByType($pageTypes);

        $CMSPageLinkers = [];
        /** @var Page $page */
        foreach ($pages as $page) {
            $CMSPageLinkers[] = new PageLinkerDto($page->getCode(), $page->getTitle());
        }

        return $CMSPageLinkers;
    }

    /**
     * @return PageLinkerDto[]|array
     */
    private function getRoutePageLinkers(): array
    {
        $routesLinkers = [];

        foreach (self::INTERNAL_ROUTES_TO_LINK as $routeName) {
            $routeTitle = $this->translator->trans('app.ui.page_linker.' . $routeName);
            $routesLinkers[] = new PageLinkerDto($routeName, $routeTitle);
        }

        return $routesLinkers;
    }
}
