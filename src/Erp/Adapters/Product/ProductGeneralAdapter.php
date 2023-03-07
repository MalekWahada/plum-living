<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Model\Erp\ErpItemModel;

class ProductGeneralAdapter implements ProductAdapterInterface
{
    /**
     * @param Product $product
     * @param ErpItemModel $erpItem
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        // set selection type "Option de correspondance de variante"
        $product->setVariantSelectionMethod(Product::VARIANT_SELECTION_MATCH);
    }
}
