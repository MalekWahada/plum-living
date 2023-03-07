<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Entity\Product\ProductOption;
use App\Entity\Taxonomy\Taxon;
use App\Model\Erp\ErpItemModel;
use App\Provider\Product\ProductOptionProvider;

class ProductOptionsAdapter implements ProductAdapterInterface
{
    private ProductOptionProvider $productOptionProvider;

    public static function getDefaultPriority(): int
    {
        // must be loaded after Category adapter !
        return -1;
    }

    public function __construct(ProductOptionProvider $productOptionProvider)
    {
        $this->productOptionProvider = $productOptionProvider;
    }

    /**
     * Options are COUPLED to the ERP
     * @param Product $product
     * @param ErpItemModel $erpItem
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        if (null === $product->getMainTaxon()) {
            return;
        }

        if (in_array($product->getMainTaxon()->getCode(), [Taxon::TAXON_FACADE_METOD, Taxon::TAXON_FACADE_PAX], true)) {
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_OPTION_DESIGN));
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_OPTION_FINISH));
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_OPTION_COLOR));
        } elseif (Taxon::TAXON_ACCESSORY_HANDLE_CODE === $product->getMainTaxon()->getCode()) {
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_HANDLE_OPTION_DESIGN));
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_HANDLE_OPTION_FINISH));
        } elseif (Taxon::TAXON_TAP_CODE === $product->getMainTaxon()->getCode()) {
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_TAP_OPTION_DESIGN));
            $product->addOption($this->productOptionProvider->provide(ProductOption::PRODUCT_TAP_OPTION_FINISH));
        }
    }
}
