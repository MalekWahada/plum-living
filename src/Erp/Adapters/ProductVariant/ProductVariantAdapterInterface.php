<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\AdapterInterface;
use App\Model\Erp\ErpItemModel;

interface ProductVariantAdapterInterface extends AdapterInterface
{
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void;
}
