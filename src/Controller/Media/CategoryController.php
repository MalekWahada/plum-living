<?php

declare(strict_types=1);

namespace App\Controller\Media;

use App\Entity\Page\Page;
use App\Provider\CMS\Chip\ChipProvider;
use App\Provider\CMS\Page\PageProvider;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CategoryController extends AbstractController
{
    private Environment $twig;
    private PageProvider $pageProvider;
    private TranslatorInterface $translator;
    private ChipProvider $chipProvider;
    private LocaleContextInterface $localeContext;

    public function __construct(
        Environment $twig,
        PageProvider $pageProvider,
        TranslatorInterface $translator,
        ChipProvider $chipProvider,
        LocaleContextInterface $localeContext
    ) {
        $this->twig         = $twig;
        $this->pageProvider = $pageProvider;
        $this->translator   = $translator;
        $this->chipProvider = $chipProvider;
        $this->localeContext = $localeContext;
    }

    public function index(Request $request, string $category): Response
    {
        $locale = $this->localeContext->getLocaleCode();
        $limit     = 12;
        $page      = (int) $request->get('page', 1);
        if ($page < 1) {
            $page  = 1;
        }

        $template      = 'category';
        if (in_array($category, ChipProvider::CHIPS_TYPES)) {
            $paginator = $this->getPaginatorFromCategory($locale, $category, $page, $limit);
        } elseif (array_key_exists($category, $this->chipProvider->getThemes($locale))) {
            $template  = 'theme';
            $paginator = $this->getPaginatorFromTheme(
                $locale,
                $this->chipProvider->getThemes($locale)[$category],
                $page,
                $limit
            );
        } else {
            throw new NotFoundHttpException(
                $this->translator->trans('app.cms_page.media_article.category.not_found')
            );
        }

        return new Response(
            $this->twig->render('Shop/Plum/Media/' . $template . '.html.twig', [
                'category'    => $category,
                'categoryTranslationKey' => $this->chipProvider->getThemeTranslationKey($category, $locale),
                'items'       => $paginator->getIterator(),
                'total'       => $paginator->count(),
                'currentPage' => $page,
                'totalPages'  => (int) ceil($paginator->count() / $limit),
            ])
        );
    }

    private function getPaginatorFromCategory(
        string $locale,
        string $category,
        int    $page,
        int    $limit
    ): Paginator {
        return $this->pageProvider->getPagesEnabledByTypeAndCategoryForLocale(
            $locale,
            Page::PAGE_TYPE_MEDIA_ARTICLE,
            $category,
            $page - 1,
            $limit
        );
    }

    private function getPaginatorFromTheme(
        string $locale,
        int    $theme,
        int    $page,
        int    $limit
    ): Paginator {
        return $this->pageProvider->getPagesEnabledByTypeAndThemeForLocale(
            $locale,
            Page::PAGE_TYPE_MEDIA_ARTICLE,
            $theme,
            $page - 1,
            $limit
        );
    }
}
