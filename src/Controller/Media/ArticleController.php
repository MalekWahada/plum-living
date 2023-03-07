<?php

declare(strict_types=1);

namespace App\Controller\Media;

use App\Entity\Page\Page;
use App\Provider\CMS\Chip\ChipProvider;
use App\Repository\Page\PageRepository;
use MonsieurBiz\SyliusCmsPagePlugin\Entity\PageInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    private PageRepository $pageRepository;
    private TranslatorInterface $translator;
    private ChipProvider $chipProvider;
    private LocaleContextInterface $localeContext;

    public function __construct(
        PageRepository $pageRepository,
        TranslatorInterface $translator,
        ChipProvider $chipProvider,
        LocaleContextInterface $localeContext
    ) {
        $this->pageRepository = $pageRepository;
        $this->translator = $translator;
        $this->chipProvider = $chipProvider;
        $this->localeContext = $localeContext;
    }

    public function index(Request $request, string $category, string $slug): Response
    {
        $locale = $this->localeContext->getLocaleCode();
        if (in_array($category, ChipProvider::CHIPS_TYPES)) {
            $page = $this->getPageFromCategory($category, Page::PAGE_TYPE_MEDIA_ARTICLE . '/' . $slug);
        } elseif (array_key_exists($category, $this->chipProvider->getThemes($locale))) {
            $page = $this->getPageFromTheme(
                $this->chipProvider->getThemes($locale)[$category],
                Page::PAGE_TYPE_MEDIA_ARTICLE . '/' . $slug
            );
        } else {
            throw new NotFoundHttpException(
                $this->translator->trans('app.cms_page.media_article.category.not_found')
            );
        }

        if (! $page) {
            throw new NotFoundHttpException(
                $this->translator->trans('app.cms_page.media_article.page.not_found')
            );
        }

        return $this->forward(
            'monsieurbiz_cms_page.controller.page:showAction',
            [
                '_sylius' => [
                    'template' => "@MonsieurBizSyliusCmsPagePlugin/Shop/Page/show.html.twig",
                    'repository' => [
                        'method' => 'findOneForPreviewByPageId',
                        'arguments' => [
                            $page->getId(),
                        ]
                    ]
                ],
            ],
        );
    }

    private function getPageFromCategory(string $category, string $slug): ?PageInterface
    {
        return $this->pageRepository->findOneBySlugAndCategoryForLocale(
            $this->localeContext->getLocaleCode(),
            $category,
            $slug
        );
    }

    private function getPageFromTheme(int $theme, string $slug): ?PageInterface
    {
        return $this->pageRepository->findOneBySlugAndThemeForLocale(
            $this->localeContext->getLocaleCode(),
            $theme,
            $slug
        );
    }
}
