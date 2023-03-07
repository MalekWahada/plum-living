<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Product\ProductOptionValue;
use App\Formatter\CMS\TextToArrayFormatter;
use App\Provider\CMS\PagesSkeleton\PagesSkeletonProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CMSExtension extends AbstractExtension
{
    private const EMPTY_TEXT_CODE = [
        'code' => 'monsieurbiz.text',
        'data' =>
            [
                'content' => ''
            ]
    ];

    public function getFilters(): array
    {
        return [
            /** Content filters */
            new TwigFilter('get_home_ui_element', [$this, 'getHomeUiElement']),
            new TwigFilter('get_article_ui_element', [$this, 'getArticleUiElement']),
            new TwigFilter('get_project_ui_element', [$this, 'getProjectUiElement']),
            new TwigFilter('get_concept_ui_element', [$this, 'getConceptUiElement']),
            new TwigFilter('get_design_finish_ui_element', [$this, 'getDesignFinishUiElement']),
            new TwigFilter('get_quotation_home_ui_element', [$this, 'getQuotationHomeUiElement']),
            new TwigFilter('get_find_installer_ui_element', [$this, 'getFindInstallerUiElement']),
            new TwigFilter('get_conception_home_ui_element', [$this, 'getConceptionHomeUiElement']),
            new TwigFilter('get_terra_club_ui_element', [$this, 'getTerraClubUiElement']),
            new TwigFilter('get_product_complete_info_ui_element', [$this, 'getProductCompleteInfoUiElement']),
            /** Custom content filters */
            new TwigFilter('get_project_total', [CMSRuntime::class, 'getTotal']),
            new TwigFilter('filter_images', [CMSRuntime::class, 'filterImages']),
            new TwigFilter('get_lecture_time', [CMSRuntime::class, 'getLectureTime']),
            new TwigFilter('get_project_color', [CMSRuntime::class, 'getProjectColor']),
            new TwigFilter('get_text_color', [CMSRuntime::class, 'getTextColor']),
            new TwigFilter('get_cross_content_color', [CMSRuntime::class, 'getCrossContentColor']),
            new TwigFilter('get_absolute_url', [CMSRuntime::class, 'getAbsoluteUrl']),
            new TwigFilter('format_cms_content_to_array', [$this, 'textToArray']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_color_from_page', [CMSRuntime::class, 'getColorFromPage']),
            new TwigFunction('get_page_content', [CMSRuntime::class, 'getPageContent']),
            new TwigFunction('get_url_from_page_code', [CMSRuntime::class, 'getUrlFromPageCode']),
            new TwigFunction('get_url_from_page', [CMSRuntime::class, 'getUrlFromPage']),
            new TwigFunction('get_linker_redirect_url', [CMSRuntime::class, 'getLinkerRedirectionUrl']),
            new TwigFunction('get_shoppinglist_ikea', [CMSRuntime::class, 'getShoppingListIkea']),
            new TwigFunction('get_shoppinglist_plum', [CMSRuntime::class, 'getShoppingListPlum']),
        ];
    }

    public function getHomeUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::HOME_MAIN_SLIDER:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::HOME_MAIN_TITLE:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::HOME_IMAGES_STEPS_DESC:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::HOME_IMAGES_STEPS:
                $uiContent = $this->getValue($content, 3);
                break;
            case PagesSkeletonProvider::HOME_DISCOVER_CONCEPT_BUTTON:
                $uiContent = $this->getValue($content, 4);
                break;
            case PagesSkeletonProvider::HOME_CREATION_BANNER:
                $uiContent = $this->getValue($content, 5);
                break;
            case PagesSkeletonProvider::HOME_SCAN_BANNER:
                $uiContent = $this->getValue($content, 6);
                break;
            case PagesSkeletonProvider::HOME_OPTION_COLOR_TITLE:
                $uiContent = $this->getValue($content, 7);
                break;
            case PagesSkeletonProvider::HOME_OPTION_COLOR_CODE:
                $uiContent = $this->getValue($content, 8);
                break;
            case PagesSkeletonProvider::HOME_DESIGN_FINISH_BUTTON:
                $uiContent = $this->getValue($content, 9);
                break;
            case PagesSkeletonProvider::HOME_ORDER_BUTTON:
                $uiContent = $this->getValue($content, 10);
                break;
            case PagesSkeletonProvider::HOME_OTHER_PAGES_TITLE:
                $uiContent = $this->getValue($content, 11);
                break;
            case PagesSkeletonProvider::HOME_OTHER_PAGES:
                $uiContent = $this->getValue($content, 12);
                break;
            case PagesSkeletonProvider::HOME_DISCOVER_OTHER_PAGES_BUTTON:
                $uiContent = $this->getValue($content, 13);
                break;
            case PagesSkeletonProvider::HOME_MANUFACTURING_TITLE:
                $uiContent = $this->getValue($content, 14);
                break;
            case PagesSkeletonProvider::HOME_MANUFACTURING_DESC:
                $uiContent = $this->getValue($content, 15);
                break;
            case PagesSkeletonProvider::HOME_MANUFACTURING_BUTTON:
                $uiContent = $this->getValue($content, 16);
                break;
            case PagesSkeletonProvider::HOME_MANUFACTURING_IMAGES:
                $uiContent = $this->getValue($content, 17);
                break;
            case PagesSkeletonProvider::HOME_MOSAIC_IMAGES_TITLE:
                $uiContent = $this->getValue($content, 18);
                break;
            case PagesSkeletonProvider::HOME_MOSAIC_IMAGES:
                $uiContent = $this->getValue($content, 19);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }

    public function getArticleUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::ARTICLE_CHIP:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::ARTICLE_MAIN_TITLE:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::ARTICLE_SHORT_DESC:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::ARTICLE_READ_BUTTON:
                $uiContent = $this->getValue($content, 3);
                break;
            case PagesSkeletonProvider::ARTICLE_CONTENT:
                $uiContent = array_slice($content, 4, count($content)-9);
                break;
            case PagesSkeletonProvider::ARTICLE_DISCOVER_OTHER_PROJECTS_TITLE:
                $uiContent = $this->getValue($content, count($content)-5);
                break;
            case PagesSkeletonProvider::ARTICLE_DISCOVER_OTHER_PROJECTS_BUTTON:
                $uiContent = $this->getValue($content, count($content)-4);
                break;
            case PagesSkeletonProvider::ARTICLE_OTHER_PAGES_TITLE:
                $uiContent = $this->getValue($content, count($content)-3);
                break;
            case PagesSkeletonProvider::ARTICLE_OTHER_PAGES_BACK_BUTTON:
                $uiContent = $this->getValue($content, count($content)-2);
                break;
            case PagesSkeletonProvider::ARTICLE_OTHER_PAGES:
                $uiContent = $this->getValue($content, count($content)-1);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }
        return $uiContent;
    }

    public function getProjectUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::PROJECT_MAIN_TITLE:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::PROJECT_COLOR:
                $colorValue = $this->getValue($content, 1);
                if (isset($colorValue["data"])) {
                    $colorCode = $colorValue["data"]["color"] ?? null;
                    if (in_array($colorCode, ProductOptionValue::HIDDEN_COLORS, true)) {
                        $colorValue["data"]["color"] = [];
                    }
                }
                $uiContent = $colorValue;
                break;
            case PagesSkeletonProvider::PROJECT_PIECE_TYPE:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::PROJECT_SHORT_DESC:
                $uiContent = $this->getValue($content, 3);
                break;
            case PagesSkeletonProvider::PROJECT_SLIDER_COLLECTION:
                $uiContent = $this->getValue($content, 4);
                break;
            case PagesSkeletonProvider::PROJECT_DISCOVER_BUDGET_BUTTON:
                $uiContent = $this->getValue($content, 5);
                break;
            case PagesSkeletonProvider::PROJECT_LONG_DESC:
                $uiContent = $this->getValue($content, 6);
                break;
            case PagesSkeletonProvider::PROJECT_BUDGET:
                $uiContent = $this->getValue($content, 7);
                break;
            case PagesSkeletonProvider::PROJECT_SLIDER_PLANS:
                $uiContent = $this->getValue($content, 8);
                break;
            case PagesSkeletonProvider::PROJECT_PLAN_FILE:
                $uiContent = $this->getValue($content, 9);
                break;
            case PagesSkeletonProvider::PROJECT_DISCOVER_TUTORIAL_LINK:
                $uiContent = $this->getValue($content, 10);
                break;
            case PagesSkeletonProvider::PROJECT_MOSAIC_COLLECTION:
                $uiContent = $this->getValue($content, 11);
                break;
            case PagesSkeletonProvider::PROJECT_ESTIMATE_MY_PROJECT_TITLE:
                $uiContent = $this->getValue($content, 12);
                break;
            case PagesSkeletonProvider::PROJECT_ESTIMATE_MY_PROJECT_BUTTON:
                $uiContent = $this->getValue($content, 13);
                break;
            case PagesSkeletonProvider::PROJECT_OTHER_PAGES_TITLE:
                $uiContent = $this->getValue($content, 14);
                break;
            case PagesSkeletonProvider::PROJECT_OTHER_PAGES_BACK_BUTTON:
                $uiContent = $this->getValue($content, 15);
                break;
            case PagesSkeletonProvider::PROJECT_OTHER_PAGES:
                $uiContent = $this->getValue($content, 16);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }
        return $uiContent;
    }

    public function getConceptUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::CONCEPT_MAIN_TITLE:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::CONCEPT_MAIN_DESC:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::CONCEPT_SCHEMA_IMAGE:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::CONCEPT_STEPS_MAIN_TITLE:
                $uiContent = $this->getValue($content, 3);
                break;
            case PagesSkeletonProvider::CONCEPT_STEPS_IMAGES:
                $uiContent = $this->getValue($content, 4);
                break;
            case PagesSkeletonProvider::CONCEPT_TUTORIAL_TITLE:
                $uiContent = $this->getValue($content, 5);
                break;
            case PagesSkeletonProvider::CONCEPT_TUTORIAL_DESC:
                $uiContent = $this->getValue($content, 6);
                break;
            case PagesSkeletonProvider::CONCEPT_TUTORIAL_DISCOVER_ARTICLE_LINK:
                $uiContent = $this->getValue($content, 7);
                break;
            case PagesSkeletonProvider::CONCEPT_TUTORIAL_CHECK_FAQ:
                $uiContent = $this->getValue($content, 8);
                break;
            case PagesSkeletonProvider::CONCEPT_TUTORIAL_VIDEO:
                $uiContent = $this->getValue($content, 9);
                break;
            case PagesSkeletonProvider::CONCEPT_TUTORIAL_DESIGN_FINISH_BUTTON:
                $uiContent = $this->getValue($content, 10);
                break;
            case PagesSkeletonProvider::CONCEPT_OTHER_PAGES_TITLE:
                $uiContent = $this->getValue($content, 11);
                break;
            case PagesSkeletonProvider::CONCEPT_OTHER_PAGES:
                $uiContent = $this->getValue($content, 12);
                break;
            case PagesSkeletonProvider::CONCEPT_OTHER_PAGES_DISCOVER_BUTTON:
                $uiContent = $this->getValue($content, 13);
                break;
            case PagesSkeletonProvider::CONCEPT_INSPIRATION_TITLE:
                $uiContent = $this->getValue($content, 14);
                break;
            case PagesSkeletonProvider::CONCEPT_INSPIRATION_MOSAIC_IMAGES:
                $uiContent = $this->getValue($content, 15);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }
        return $uiContent;
    }

    public function getDesignFinishUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::DESIGN_FINISH_MAIN_TITLE:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OPTION_DESIGN_TITLE:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OPTION_DESIGN_CODE:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OPTION_FINISH_TITLE:
                $uiContent = $this->getValue($content, 3);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OPTION_FINISH_CODE:
                $uiContent = $this->getValue($content, 4);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OPTION_COLOR_TITLE:
                $uiContent = $this->getValue($content, 5);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OPTION_COLOR_CODE:
                $uiContent = $this->getValue($content, 6);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_SEE_SAMPLE_TEXT:
                $uiContent = $this->getValue($content, 7);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_SEE_SAMPLE_LINK:
                $uiContent = $this->getValue($content, 8);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_SEE_SAMPLE_IMAGE:
                $uiContent = $this->getValue($content, 9);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OTHER_PAGES_TITLE:
                $uiContent = $this->getValue($content, 10);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_OTHER_PAGES:
                $uiContent = $this->getValue($content, 11);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_DISCOVER_OTHER_PAGES_BUTTON:
                $uiContent = $this->getValue($content, 12);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_MOSAIC_IMAGES_TITLE:
                $uiContent = $this->getValue($content, 13);
                break;
            case PagesSkeletonProvider::DESIGN_FINISH_MOSAIC_IMAGES:
                $uiContent = $this->getValue($content, 14);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }

    public function getQuotationHomeUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::QUOTATION_HOME_MAIN_TITLE:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::QUOTATION_HOME_PAGES_CARDS:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::QUOTATION_HOME_CARDS:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::QUOTATION_HOME_BUTTON:
                $uiContent = $this->getValue($content, 3);
                break;
            case PagesSkeletonProvider::QUOTATION_HOME_CROSS_CONTENT_TITLE:
                $uiContent = $this->getValue($content, 4);
                break;
            case PagesSkeletonProvider::QUOTATION_HOME_CROSS_CONTENT_BACK_LINK:
                $uiContent = $this->getValue($content, 5);
                break;
            case PagesSkeletonProvider::QUOTATION_HOME_CROSS_CONTENT_PAGES:
                $uiContent = $this->getValue($content, 6);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }

    public function getFindInstallerUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::FIND_INSTALLER_MAIN_TITLE:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::FIND_INSTALLER_CROSS_CONTENT_TITLE:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::FIND_INSTALLER_CROSS_CONTENT_BACK_LINK:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::FIND_INSTALLER_CROSS_CONTENT_PAGES:
                $uiContent = $this->getValue($content, 3);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }

    public function getConceptionHomeUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::CONCEPTION_HOME_BUTTON:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::CONCEPTION_HOME_CROSS_CONTENT_TITLE:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::CONCEPTION_HOME_CROSS_CONTENT_BACK_LINK:
                $uiContent = $this->getValue($content, 2);
                break;
            case PagesSkeletonProvider::CONCEPTION_HOME_CROSS_CONTENT_PAGES:
                $uiContent = $this->getValue($content, 3);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }

    public function getTerraClubUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::TERRA_CLUB_CROSS_CONTENT_TITLE:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::TERRA_CLUB_CROSS_CONTENT_BACK_LINK:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::TERRA_CLUB_CROSS_CONTENT_PAGES:
                $uiContent = $this->getValue($content, 2);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }


    public function getProductCompleteInfoUiElement(?array $content, string $uiCode): array
    {
        if ($content === null) {
            return self::EMPTY_TEXT_CODE;
        }

        switch ($uiCode) {
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_DETAIL:
                $uiContent = $this->getValue($content, 0);
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_QUESTIONS:
                $uiContent = $this->getValue($content, 1);
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_MOSAIC_IMAGES:
                $uiContent = count($content) === 8 ?
                    $uiContent = $this->getValue($content, 2) : self::EMPTY_TEXT_CODE;
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_EXAMPLES_TITLE:
                $uiContent = $this->getValue($content, count($content)-5);
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_EXAMPLES_BUTTON_LINK:
                $uiContent = $this->getValue($content, count($content)-4);
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_CROSS_CONTENT_TITLE:
                $uiContent = $this->getValue($content, count($content)-3);
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_INSPIRATIONS_BUTTON_LINK:
                $uiContent = $this->getValue($content, count($content)-2);
                break;
            case PagesSkeletonProvider::PRODUCT_COMPLETE_INFO_CROSS_CONTENT:
                $uiContent = $this->getValue($content, count($content)-1);
                break;
            default:
                $uiContent = self::EMPTY_TEXT_CODE;
        }

        return $uiContent;
    }

    public function textToArray(string $text): ?array
    {
        return TextToArrayFormatter::format($text);
    }

    private function getValue(array $array, int $key): array
    {
        return array_key_exists($key, $array) ? $array[$key] : self::EMPTY_TEXT_CODE;
    }
}
