<?php

declare(strict_types=1);

namespace App\Transformer\ProductVariant;

use App\Dto\ProductVariant\ProductVariantDto;
use App\Model\ProductVariant\ProductVariantModel;
use App\Repository\Product\ProductVariantRepository;

final class ProductVariantModelTransformer
{
    private ProductVariantRepository $productVariantRepository;

    public function __construct(ProductVariantRepository $productVariantRepository)
    {
        $this->productVariantRepository = $productVariantRepository;
    }

    /**
     * before creating a single valid "ProductVariantModel",
     * we have to ensure that code/quantity props exist and are valid.
     * the product variant code have to exist in the database.
     * the quantity string should be suitable for a string to int conversion.
     *
     * @param ProductVariantDto $productVariant
     * @return ProductVariantModel|null
     */
    public function transform(ProductVariantDto $productVariant): ?ProductVariantModel
    {
        $code = $this->getProductVariantCode($productVariant->getCode());
        $quantity = intval($productVariant->getQuantity());

        if (null === $code || $quantity === 0) {
            return null;
        }

        return new ProductVariantModel($code, $quantity);
    }

    private function getProductVariantCode(string $code): ?string
    {
        $productVariantObj = $this->productVariantRepository->findOneBy([
            'code' => $code,
        ]);

        return $productVariantObj !== null ? $code : null;
    }
}
