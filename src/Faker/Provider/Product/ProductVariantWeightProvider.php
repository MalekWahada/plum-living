<?php

declare(strict_types=1);

namespace App\Faker\Provider\Product;

use Faker\Provider\Base as BaseProvider;

final class ProductVariantWeightProvider extends BaseProvider
{
    private const PRODUCT_VARIANT_WEIGHT_PROVIDER = [
        null,
        1.49,
        0.093,
        4.703,
        2.051,
        2.267,
    ];

    public function productVariantWeight(): float
    {
        return array_rand(self::PRODUCT_VARIANT_WEIGHT_PROVIDER);
    }
}
