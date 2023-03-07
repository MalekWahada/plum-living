<?php

declare(strict_types=1);

namespace App\Provider\Product;

use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use App\Repository\Product\ProductRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductProvider
{
    private const TAXONS_SORT_ORDER = [
        Taxon::TAXON_FACADE_METOD_DRAWER_CODE,
        Taxon::TAXON_FACADE_METOD_DOOR_CODE,
        Taxon::TAXON_FACADE_PAX_DOOR_CODE,
        Taxon::CUSTOM_TAXON_PANEL_AND_PLINTH_CODE,
        Taxon::TAXON_ACCESSORY_CODE,
    ];

    private const REST_PRODUCT_CATEGORY_TAXON = [
        Taxon::TAXON_FACADE_METOD_DOOR_CODE,
        Taxon::TAXON_FACADE_PAX_DOOR_CODE,
        Taxon::TAXON_FACADE_METOD_DRAWER_CODE,
    ];

    private ProductRepository $productRepository;
    private TaxonRepositoryInterface $taxonRepository;
    private TranslatorInterface $translator;
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;

    public function __construct(
        ProductRepository $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        TranslatorInterface $translator,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->productRepository = $productRepository;
        $this->taxonRepository = $taxonRepository;
        $this->translator = $translator;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
    }

    /**
     * Get a list of products classed by category.
     * The list of products is based on  the facade type, design finish and color
     *
     * @param Taxon $facadeTypeTaxon
     * @param ProductOptionValue|null $design
     * @param ProductOptionValue|null $finish
     * @param ProductOptionValue|null $color
     * @return array<string, array<string, mixed>>
     */
    public function getProductPerTaxon(
        Taxon $facadeTypeTaxon,
        ProductOptionValue $design = null,
        ProductOptionValue $finish = null,
        ProductOptionValue $color = null
    ): ?array {
        $productsPerTaxon = [];
        $accessoryTaxon = $this->taxonRepository->findOneBy(['code' => Taxon::TAXON_ACCESSORY_CODE]);

        $channel = $this->channelContext->getChannel();

        $allProducts= $this->productRepository
            ->getEnabledProductsByFacadeDesignFinishColor(
                $facadeTypeTaxon,
                $accessoryTaxon,
                $design,
                $finish,
                $color,
                $channel->getCode()
            );

        $plinthTaxonTranslation = $this->translator->trans('app.facade.tunnel_shopping.panels_and_plinths');

        foreach ($allProducts as $product) {
            if (null === $product || null === $product->getMainTaxon()) {
                continue;
            }
            if (Taxon::TAXON_ACCESSORY_CODE === $product->getMainTaxon()->getCode()) {
                $productsPerTaxon[$product->getMainTaxon()->getCode()]['taxonName'] = $product->getMainTaxon()->getName();
                $productsPerTaxon[$product->getMainTaxon()->getCode()]['products'][] = $product;
            } else {
                foreach ($product->getTaxons() as $taxon) {
                    $code = $taxon->getCode();
                    if (in_array($code, self::REST_PRODUCT_CATEGORY_TAXON, true)) {
                        $productsPerTaxon[$code]['taxonName'] = $taxon->getName();
                        $productsPerTaxon[$code]['products'][] = $product;
                        break;
                    }

                    if (in_array($code, Taxon::CUSTOM_TAXONS_PANEL_AND_PLINTH_CODES, true)) { // Group panels and plinths
                        $productsPerTaxon[Taxon::CUSTOM_TAXON_PANEL_AND_PLINTH_CODE]['taxonName'] = $plinthTaxonTranslation;
                        $productsPerTaxon[Taxon::CUSTOM_TAXON_PANEL_AND_PLINTH_CODE]['products'][] = $product;
                        break;
                    }
                }
            }
        }

        // Sort by custom order
        uksort($productsPerTaxon, static fn ($a, $b) => array_search($a, self::TAXONS_SORT_ORDER, true) <=> array_search($b, self::TAXONS_SORT_ORDER, true));
        return $productsPerTaxon;
    }

    public function getProductBySlug(string $slug): ?ProductInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        return $this->productRepository->findOneByChannelAndSlug(
            $channel,
            $this->localeContext->getLocaleCode(),
            $slug
        );
    }
}
