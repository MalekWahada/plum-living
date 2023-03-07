<?php

declare(strict_types=1);

namespace App\Twig;

use App\Calculator\ProductPriceTaxCalculator;
use App\Entity\Page\Page;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Entity\ProductIkea\ProductIkea;
use App\Provider\CMS\Link\CMSLinkProvider;
use App\Provider\CMS\Link\PageLinkerProvider;
use App\Provider\CMS\Page\PageProvider;
use App\Provider\CMS\PageContent\PageContentProvider;
use App\Provider\CMS\ProductOptionColor\ProductOptionColorProvider;
use App\Provider\Image\ColorProvider;
use App\Repository\Product\ProductOptionValueRepository;
use App\Repository\Product\ProductVariantRepository;
use App\Repository\ProductIkea\ProductIkeaRepository;
use MonsieurBiz\SyliusCmsPagePlugin\Entity\PageInterface;
use MonsieurBiz\SyliusCmsPagePlugin\Entity\PageTranslationInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CMSRuntime implements RuntimeExtensionInterface
{
    private PageProvider $pageProvider;
    private PageContentProvider $pageContentProvider;
    private ColorProvider $colorProvider;
    private ProductPriceTaxCalculator $priceWithTaxCalculator;
    private ProductIkeaRepository $productIkeaRepository;
    private ProductVariantRepository $productVariantRepository;
    private ProductOptionValueRepository $productOptionValueRepository;
    private ProductOptionColorProvider $productOptionColorProvider;
    private CMSLinkProvider $CMSLinkProvider;
    private PageLinkerProvider $linkerProvider;
    private RouterInterface $router;
    private ChannelContextInterface $channelContext;

    public function __construct(
        PageProvider $pageProvider,
        PageContentProvider $pageContentProvider,
        ColorProvider $colorProvider,
        ProductPriceTaxCalculator $priceWithTaxCalculator,
        ProductIkeaRepository $productIkeaRepository,
        ProductVariantRepository $productVariantRepository,
        ProductOptionValueRepository $productOptionValueRepository,
        ProductOptionColorProvider $productOptionColorProvider,
        CMSLinkProvider $CMSLinkProvider,
        PageLinkerProvider $linkerProvider,
        RouterInterface $router,
        ChannelContextInterface $channelContext
    ) {
        $this->pageProvider = $pageProvider;
        $this->pageContentProvider = $pageContentProvider;
        $this->colorProvider = $colorProvider;
        $this->priceWithTaxCalculator = $priceWithTaxCalculator;
        $this->productIkeaRepository = $productIkeaRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->productOptionColorProvider = $productOptionColorProvider;
        $this->CMSLinkProvider = $CMSLinkProvider;
        $this->linkerProvider = $linkerProvider;
        $this->router = $router;
        $this->channelContext = $channelContext;
    }

    /**
     * calculate the total budget of a page type project from
     * the total budget elements in the back office.
     * @param array|null $content
     * @return float|null
     */
    public function getTotal(?array $content): ?float
    {
        return $this->pageContentProvider->calculateTotal($content);
    }

    /**
     * returns the first image from productOptionValue images that
     * according  to the given type.
     * @param ProductOptionValue $productOptionValue
     * @param string $type
     * @return ImageInterface|null
     */
    public function filterImages(ProductOptionValue $productOptionValue, string $type): ?ImageInterface
    {
        $images = $productOptionValue->getImagesByType($type);

        return !$images->isEmpty() ? $images->first() : null;
    }

    /**
     * return an approximate lecture time of an article page.
     * @param array|null $content
     * @return int|null
     */
    public function getLectureTime(?array $content): ?int
    {
        return $this->pageContentProvider->getLectureTime($content);
    }

    /**
     * since the home page is part of cms and its content can't be rendered
     * automatically in the shop layout, we need this function to get
     * the home's page content.
     * @param string $pageType
     * @param string|null $pageCode
     * @return Page|null
     */
    public function getPageContent(string $pageType, ?string $pageCode = null): ?Page
    {
        return $this->pageProvider->getPageByTypeAndCode($pageType, $pageCode);
    }

    public function getShoppingListIkea(array $ikea): array
    {
        $codes = array_map(static function ($item) {
            return $item['ikea']['product'] ?: null;
        }, $ikea);
        $products = $this->productIkeaRepository->findBy(['code' => $codes]);
        usort($products, function (ProductIkea $a, ProductIkea $b) use ($codes) {
            $posA = array_search($a->getCode(), $codes);
            $posB = array_search($b->getCode(), $codes);
            return ($posA < $posB) ? -1 : 1;
        });
        $items = [];
        $total = 0;
        /** @var ProductIkea $product */
        foreach ($products as $product) {
            $channelPricing = $product->getChannelPricingForChannel($this->channelContext->getChannel());
            if (!$channelPricing) {
                continue;
            }
            $price = $channelPricing->getPrice();
            $element = [
                'code'  => $product->getCode(),
                'name'  => $product->getName(),
                'price' => $price,
                'image' => $product->getImage()->getPath(),
            ];
            foreach ($ikea as $item) {
                $code = $item['ikea']['product'] ?: null;
                if ($code == $product->getCode() && !isset($element['quantity'])) {
                    $element['quantity'] = $item['quantity'];
                    $total += $item['quantity'] * $price;
                }
            }
            $items[] = $element;
        }

        return ['total' => $total, 'items' => $items];
    }

    public function getShoppingListPlum(array $plum): array
    {
        $codes = array_map(static function ($item) {
            return $item['plum']['product_variant'] ?: null;
        }, $plum);
        $variants = $this->productVariantRepository->findBy(['code' => array_filter($codes)]);
        usort($variants, function (ProductVariant $a, ProductVariant $b) use ($codes) {
            $posA = array_search($a->getCode(), $codes);
            $posB = array_search($b->getCode(), $codes);
            return ($posA < $posB) ? -1 : 1;
        });
        $items = [];
        $total = 0;
        foreach ($variants as $variant) {
            /** @var ProductInterface $product */
            /** @var ProductVariant $variant */
            $product = $variant->getProduct();
            $image   = $product->getImages()->first();
            $price   = $this->priceWithTaxCalculator->calculate($variant);
            $name    = explode('- ', $variant->getTranslation()->getName())[0] ?? $variant->getTranslation()->getName();
            $name    = explode('|', $name)[0] ?? $name;
            $values  = $variant->getOptionValues()->map(function (ProductOptionValueInterface $optionValue) use (&$name) {
                $value = $optionValue->getValue();
                if (!$value) {
                    return null;
                }
                $name = preg_replace('/\| ' . $value . '/i', '$1', $name);

                return preg_replace('/\(.*\)/', '$1', $value);
            })->getValues();
            $element = [
                'code' => $variant->getCode(),
                'name' => $name,
                'price' => $price,
                'image' => $image ? $image->getPath() : null,
                'values' => array_filter($values),
            ];
            foreach ($plum as $item) {
                $code = $item['plum']['product_variant'] ?: null;
                if ($code == $variant->getCode() && !isset($element['quantity'])) {
                    $element['quantity'] = $item['quantity'];
                    $total += $item['quantity'] * $price;
                }
            }
            $items[] = $element;
        }

        return ['total' => $total, 'items' => $items];
    }

    /**
     * get a cms project page defined color.
     * @param string $colorCode
     * @return string
     */
    public function getProjectColor(string $colorCode): string
    {
        $whiteColor = '#FFFFFF';
        /** @var ProductOptionValue $productOptionValue */
        $productOptionValue =  $this->productOptionValueRepository->findOneBy(['code' => $colorCode]);
        if ($productOptionValue !== null && $productOptionValue->getColorHex() !== null) {
            return $productOptionValue->getColorHex();
        }
        return $whiteColor;
    }

    /**
     * determines what color a text should have either a black color code "#000000"
     * or a white color code "#FFFFFF" depending on the given colorHex code contrast.
     * it used for a styling purpose in the single project page header.
     * @param string|null $colorHex
     * @return string
     */
    public function getTextColor(?string $colorHex): string
    {
        if ($colorHex !== null) {
            return $this->colorProvider->getContrastColor($colorHex);
        }
        return '#000000';
    }

    /**
     * since we are using a colorHex property to determine the displaying text color
     * in the cross content, white color variations may not appear.
     * so this function tracks any white color code and return a gray hex(#a9b1bb) instead.
     * @param ProductOptionValue $productOptionValue
     * @return string
     */
    public function getCrossContentColor(ProductOptionValue $productOptionValue): string
    {
        if ($productOptionValue->getColorHex() === null || $this->colorProvider->isWhite($productOptionValue->getCode())) {
            return '#a9b1bb';
        }
        return $productOptionValue->getColorHex();
    }

    public function getColorFromPage(Page $page): string
    {
        $color = $page->getColor();
        $colors = $this->productOptionColorProvider->getColorsCodes(true);
        foreach ($colors as $productOptionValue) {
            if ($color !== $productOptionValue->getCode()) {
                continue;
            }
            return $productOptionValue->getValue();
        }
        return '';
    }

    /**
     * get absolute URL of a button link by checking if it is an absolute URL or a relative one.
     * we assume that an absolute URL start a (http or https) scheme.
     * for the relative one we generate an absolute URL based on the home page router path.
     * @param string|null $url
     * @return string
     */
    public function getAbsoluteUrl(?string $url): string
    {
        return $this->CMSLinkProvider->generateAbsoluteURL($url);
    }

    /**
     * get a CMS page url (it is generated based on the home page absolute URL).
     * @param string $pageCode
     * @param string|null $pageType
     * @param string|null $localeCode
     * @return string
     */
    public function getUrlFromPageCode(string $pageCode, ?string $pageType = null, ?string $localeCode = null): string
    {
        $page = $this->pageProvider->getPageByTypeAndCode($pageType, $pageCode);
        $slug = $page !== null ? $page->getTranslation($localeCode)->getSlug() : null;

        return $this->CMSLinkProvider->generateURLFromSlug($slug);
    }

    public function getUrlFromPage(PageInterface $page, ?string $localeCode = null): string
    {
        /** @var PageTranslationInterface $translation */
        $translation = $page->getTranslation($localeCode);
        $slug = $translation->getSlug();

        if ($page instanceof Page && $page->getType() === Page::PAGE_TYPE_MEDIA_ARTICLE) {
            $needle = $page->getType() . '/';
            $substr = strstr($slug, $needle);
            if ($substr) {
                $slug = substr($substr, strlen($needle));
            }

            return $this->router->generate('app_media_article', [
                'slug' => $slug,
                'category' => $page->getCategory(),
            ]);
        }

        return $this->CMSLinkProvider->generateURLFromSlug($slug);
    }

    public function getLinkerRedirectionUrl(string $linkerCode): string
    {
        if ($this->linkerProvider->isInternalRouterLinker($linkerCode)) {
            return $this->router->generate($linkerCode, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->getUrlFromPageCode($linkerCode);
    }
}
