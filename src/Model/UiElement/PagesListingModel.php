<?php

declare(strict_types=1);

namespace App\Model\UiElement;

use App\Provider\CMS\Page\PageProvider;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementInterface;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementTrait;
use Sylius\Component\Locale\Context\LocaleContextInterface;

class PagesListingModel implements UiElementInterface
{
    use UiElementTrait;

    private PageProvider $pageProvider;
    private LocaleContextInterface $localeContext;

    public function __construct(PageProvider $pageProvider, LocaleContextInterface $localeContext)
    {
        $this->pageProvider = $pageProvider;
        $this->localeContext = $localeContext;
    }

    public function getPagesListing(array $pagesCodes): array
    {
        return $this->pageProvider->getPagesContentsForLocale($pagesCodes, $this->localeContext->getLocaleCode());
    }
}
