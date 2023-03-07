<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Erp\Adapters\AdapterInterface;
use App\Model\Erp\ErpItemModel;

interface ProductAdapterInterface extends AdapterInterface
{
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void;
}
