<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Page\Page;
use MonsieurBiz\SyliusCmsPagePlugin\Repository\PageRepositoryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PagesClassesExtension extends AbstractExtension
{
    protected RequestStack $requestStack;
    protected PageRepositoryInterface $cmsPageRepository;
    protected ChannelContextInterface $channelContext;
    protected LocaleContextInterface $localeContext;

    public function __construct(
        RequestStack $requestStack,
        PageRepositoryInterface $pageRepository,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->requestStack = $requestStack;
        $this->cmsPageRepository = $pageRepository;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCurrentRouteClasses', [$this, 'getCurrentRouteClasses']),
            new TwigFunction('getCMSPageClasses', [$this, 'getCMSPageClasses']),
        ];
    }

    public function getCurrentRouteClasses(): string
    {
        $classes = [];

        // if current request exists.
        if ($request = $this->requestStack->getCurrentRequest()) {
            // add current route to body classes
            $classes[] = $request->get('_route');
        }

        return implode(' ', $classes);
    }

    public function getCMSPageClasses(?Page $page): string
    {
        $classes = [];

        if (null !== $page) {
            $classes[] = 'CMS_' . $page->getCode();
            $classes[] = 'CMS_PAGE_TYPE_' . $page->getType();
        }

        return implode(' ', $classes);
    }
}
