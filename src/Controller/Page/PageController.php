<?php

declare(strict_types=1);

namespace App\Controller\Page;

use App\Entity\Page\Page;
use App\Repository\Page\PageRepository;
use App\Twig\CMSRuntime;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class PageController extends AbstractController
{
    private LoggerInterface $logger;
    private PageRepository $pageRepository;
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private CMSRuntime $CMSRuntime;

    public function __construct(
        CMSRuntime $CMSRuntime,
        LoggerInterface $logger,
        PageRepository $pageRepository,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->logger = $logger;
        $this->pageRepository = $pageRepository;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->CMSRuntime = $CMSRuntime;
    }

    public function showDefaultTypeOrRedirectAction(string $slug): Response
    {
        if ($this->pageRepository->existsOneByChannelAndSlugAndType(
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode(),
            $slug,
            Page::PAGE_TYPE_ARTICLE
        )) {
            $this->logger->info('Inspiration article was called from its legacy path.', [$slug]);

            return $this->redirectToRoute('monsieurbiz_cms_inspiration_page_show', ['slug' => $slug], 301);
        }

        if ($this->pageRepository->existsOneByChannelAndSlugAndType(
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode(),
            $slug,
            Page::PAGE_TYPE_PROJECT
        )) {
            $this->logger->info('Project article was called from its legacy path.', [$slug]);

            return $this->redirectToRoute('monsieurbiz_cms_project_page_show', ['slug' => $slug], 301);
        }

        // media_article
        $pageMediaArticle = strpos($slug, Page::PAGE_TYPE_MEDIA_ARTICLE) === 0;
        if ($pageMediaArticle && $page = $this->pageRepository->getOneByChannelAndSlugAndType(
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode(),
            $slug,
            Page::PAGE_TYPE_MEDIA_ARTICLE
        )) {
            $this->logger->info('Media article was called from its legacy path.', [$slug]);

            return $this->redirect($this->CMSRuntime->getUrlFromPage($page), 301);
        }

        return $this->forward(
            'monsieurbiz_cms_page.controller.page:showAction',
            [
                '_sylius' => [
                    'template' => "@MonsieurBizSyliusCmsPagePlugin/Shop/Page/show.html.twig",
                    'repository' => [
                            'method' => 'findOneEnabledBySlugAndChannelCode',
                            'arguments' => [
                                $slug,
                                "expr:service('sylius.context.locale').getLocaleCode()",
                                "expr:service('sylius.context.channel').getChannel().getCode()",
                            ]
                    ]
                ],
                '_route' => 'monsieurbiz_cms_page_show'
            ],
            [
                'slug' => $slug,
            ]
        );
    }
}
