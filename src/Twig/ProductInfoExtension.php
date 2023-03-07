<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Taxonomy\Taxon;
use App\Provider\CMS\ProductInfo\ProductInfoProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ProductInfoExtension extends AbstractExtension
{
    private ProductInfoProvider $productInfoProvider;

    public function __construct(ProductInfoProvider $productInfoProvider)
    {
        $this->productInfoProvider = $productInfoProvider;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('get_product_info', [$this, 'getProductInfo']),
        ];
    }

    // Given a child taxon and an optional facade (taxon of type PAX or METOD), we return the suitable product info.
    // This method is used mainly in the e-shop (shopping-tunnel) + the listing templates.
    public function getProductInfo(string $taxonCode, string $facadeTypeCode = ''): ?string
    {
        $isFacadePax = $facadeTypeCode === Taxon::TAXON_FACADE_PAX;

        switch ($taxonCode) {
            case Taxon::TAXON_PAINT_CODE:
                return $this->productInfoProvider->getProductInfo(Taxon::TAXON_PAINT_CODE);
            case Taxon::TAXON_ACCESSORY_CODE:
                return $this->productInfoProvider->getAccessoryProductInfo($facadeTypeCode);
            case Taxon::TAXON_FACADE_METOD_DRAWER_CODE:
                return $this->productInfoProvider->getProductInfo(Taxon::TAXON_FACADE_METOD_DRAWER_CODE);
            case Taxon::TAXON_FACADE_METOD_DOOR_CODE:
            case Taxon::TAXON_FACADE_PAX_DOOR_CODE:
                $code = $isFacadePax ? Taxon::TAXON_FACADE_PAX_DOOR_CODE : Taxon::TAXON_FACADE_METOD_DOOR_CODE;
                return $this->productInfoProvider->getProductInfo($code);
            case Taxon::CUSTOM_TAXON_PANEL_AND_PLINTH_CODE:
                $code = $isFacadePax ? Taxon::TAXON_FACADE_PAX_PANEL_CODE : Taxon::TAXON_FACADE_METOD_PANEL_CODE;
                return $this->productInfoProvider->getProductInfo($code);
            default:
                return null;
        }
    }
}
