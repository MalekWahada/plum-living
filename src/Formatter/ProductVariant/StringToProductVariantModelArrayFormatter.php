<?php

declare(strict_types=1);

namespace App\Formatter\ProductVariant;

use App\Dto\ProductVariant\ProductVariantDto;
use App\Model\ProductVariant\ProductVariantModel;
use App\Transformer\ProductVariant\ProductVariantModelTransformer;

class StringToProductVariantModelArrayFormatter
{
    private ProductVariantModelTransformer $productVariantModelTransformer;

    public function __construct(ProductVariantModelTransformer $productVariantModelTransformer)
    {
        $this->productVariantModelTransformer = $productVariantModelTransformer;
    }

    /**
     * transform received query string to an array of product variant models.
     * any bad/unset string lead to the fail of format or the transformation (object to ProductVariantModel).
     *
     * @param string|null $productVariantCodeQuantityParams
     * @return ProductVariantModel[]|array|null
     */
    public function format(?string $productVariantCodeQuantityParams): ?array
    {
        if (!is_string($productVariantCodeQuantityParams)) {
            return null;
        }

        $productVariantModels = [];
        $explodedProductVariants = explode(';', $productVariantCodeQuantityParams);

        foreach ($explodedProductVariants as $productVariantStr) {
            $productVariantModel = $this->transformToProductVariantModel($productVariantStr);
            if (null === $productVariantModel) {
                return null;
            }

            $productVariantModels[] = $productVariantModel;
        }
        return $productVariantModels;
    }

    private function transformToProductVariantModel(string $productVariantStr): ?ProductVariantModel
    {
        $productVariantDto = $this->formatStringToProductVariantDto($productVariantStr);
        if (null === $productVariantDto) {
            return null;
        }

        $productVariantModel = $this->productVariantModelTransformer->transform($productVariantDto);
        if (null === $productVariantModel) {
            return null;
        }

        return $productVariantModel;
    }

    private function formatStringToProductVariantDto(string $objectStr): ?ProductVariantDto
    {
        $explodedObject = explode('|', $objectStr);

        $code = $this->getArrayProperty($explodedObject, 0);
        $quantity = $this->getArrayProperty($explodedObject, 1);

        if (null === $code || null === $quantity) {
            return null;
        }

        return new ProductVariantDto($code, $quantity);
    }

    private function getArrayProperty(array $objArray, int $key): ?string
    {
        if (array_key_exists($key, $objArray) && !empty($objArray[$key])) {
            return $objArray[$key];
        }

        return null;
    }
}
