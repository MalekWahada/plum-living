<?php

declare(strict_types=1);

namespace App\Provider\CMS\ProductOptionCode;

use App\Entity\Product\ProductOption;

class ProductOptionCodeProvider
{
    public const PRODUCT_OPTION_CODES = [
        ProductOption::PRODUCT_OPTION_COLOR,
        ProductOption::PRODUCT_OPTION_DESIGN,
        ProductOption::PRODUCT_OPTION_FINISH,
    ];

    public function getProductOptionCodes(): array
    {
        return self::PRODUCT_OPTION_CODES;
    }
}
