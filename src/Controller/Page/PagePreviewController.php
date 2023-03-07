<?php

declare(strict_types=1);

namespace App\Controller\Page;

use App\Repository\Page\PageRepository;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PagePreviewController extends AbstractController
{
    private LoggerInterface $logger;
    private PageRepository $pageRepository;
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;

    public function __construct(
        LoggerInterface $logger,
        PageRepository $pageRepository,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->logger = $logger;
        $this->pageRepository = $pageRepository;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
    }

    public function __invoke(int $pageId): Response
    {
        return $this->forward(
            'monsieurbiz_cms_page.controller.page:showAction',
            [
                '_sylius' => [
                    'template' => "@MonsieurBizSyliusCmsPagePlugin/Shop/Page/show.html.twig",
                    'repository' => [
                        'method' => 'findOneForPreviewByPageId',
                        'arguments' => [
                            $pageId,
                        ]
                    ]
                ],
            ],
        );
    }
}
