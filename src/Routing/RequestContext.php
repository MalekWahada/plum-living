<?php

declare(strict_types=1);

namespace App\Routing;

use App\Entity\Locale\Locale;
use App\Entity\Page\Page;
use App\Model\Translation\SwitchableTranslation;
use App\Provider\Translation\SwitchableTranslationProvider;
use App\Repository\Page\PageRepository;
use Exception;
use MonsieurBiz\SyliusCmsPagePlugin\Routing\RequestContext as DecoratedRequestContext;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Routing\RequestContext as NativeRequestContext;

/**
 * Customized request context for CMS pages
 */
final class RequestContext extends NativeRequestContext
{
    private DecoratedRequestContext $decorated;
    private PageRepository $pageRepository;
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private array $pageTypesPrefixesWithTrailingSlash;
    private SwitchableTranslationProvider $translationProvider;

    public function __construct(
        DecoratedRequestContext $decorated,
        PageRepository $pageRepository,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext,
        SwitchableTranslationProvider $translationProvider
    ) {
        parent::__construct(
            $decorated->getBaseUrl(),
            $decorated->getMethod(),
            $decorated->getHost(),
            $decorated->getScheme(),
            $decorated->getHttpPort(),
            $decorated->getHttpsPort(),
            $decorated->getPathInfo(),
            $decorated->getQueryString()
        );

        $this->decorated = $decorated;
        $this->pageRepository = $pageRepository;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->translationProvider = $translationProvider;

        $this->pageTypesPrefixesWithTrailingSlash = [
            sprintf('%s/', Page::PAGE_TYPE_ARTICLE_PREFIX),
            sprintf('%s/', Page::PAGE_TYPE_PROJECT_PREFIX),
        ];
    }

    public function checkPageSlug(string $slug): bool
    {
        $pageType = null;

        // Find page types by prefixes
        if (false !== strpos($slug, sprintf('/%s/', Page::PAGE_TYPE_ARTICLE_PREFIX))) {
            $pageType = Page::PAGE_TYPE_ARTICLE;
        } elseif (false !== strpos($slug, sprintf('/%s/', Page::PAGE_TYPE_PROJECT_PREFIX))) {
            $pageType = Page::PAGE_TYPE_PROJECT;
        }

        if (null === $pageType) { // Use default checker for non prefixed slugs
            return $this->decorated->checkPageSlug($this->prepareSlug($slug));
        }

        return $this->pageRepository->existsOneByChannelAndSlugAndType(
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode(),
            $this->prepareSlug($slug),
            $pageType
        );
    }

    /**
     * Format slug and extract slug and page prefixes if any
     * @param string $slug
     * @return string
     */
    private function prepareSlug(string $slug): string
    {
        $slug = ltrim($slug, '/');
        preg_match(SwitchableTranslation::URI_SLUG_REGEX, $slug, $matches); // First match is slug

        if (sizeof($matches) < 2) {
            return str_replace($this->pageTypesPrefixesWithTrailingSlash, '', $slug);
        }

        return str_replace([sprintf('%s/', $matches[1]), ...$this->pageTypesPrefixesWithTrailingSlash], '', $slug);
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->decorated->__call($name, $arguments);
    }
}
