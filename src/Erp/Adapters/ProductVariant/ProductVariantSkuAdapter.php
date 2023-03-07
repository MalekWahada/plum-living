<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Erp\Slugifier;
use App\Model\Erp\ErpItemModel;

class ProductVariantSkuAdapter implements ProductVariantAdapterInterface
{
    /**
     * SKI is COUPLED with the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        $productVariant->setCode(Slugifier::slugifyCode($erpItem->getCode() ?? null));
    }
}
