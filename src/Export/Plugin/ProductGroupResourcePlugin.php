<?php

declare(strict_types=1);

namespace App\Export\Plugin;

use App\Entity\Product\ProductGroup;

final class ProductGroupResourcePlugin extends TranslatableResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport); // Set Id, Code, Position + Name

        /** @var ProductGroup $resource */
        foreach ($this->resources as $resource) {
            $this->addExtraData($resource);
            $this->addProducts($resource);
        }
    }

    private function addExtraData(ProductGroup $resource): void
    {
        $this->addDataForResource($resource, 'MainTaxon', $resource->getMainTaxon()->getCode());
    }

    private function addProducts(ProductGroup $resource): void
    {
        $productsSlug = '';
        foreach ($resource->getProducts() as $product) {
            $productsSlug .= $product->getCode() . '|';
        }

        $productsSlug = rtrim($productsSlug, '|');
        $this->addDataForResource($resource, 'Products', $productsSlug);
    }
}
