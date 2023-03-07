<?php

declare(strict_types=1);

namespace App\Repository\Product;

use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Repository\ProductAttributeValueRepositoryInterface as BaseProductAttributeValueRepositoryInterface;

interface ProductAttributeValueRepositoryInterface extends BaseProductAttributeValueRepositoryInterface
{
    public function findByProductAndAttribute(ProductInterface $product, AttributeInterface $attribute): array;
}
