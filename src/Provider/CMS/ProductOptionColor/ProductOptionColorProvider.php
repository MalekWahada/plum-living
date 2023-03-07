<?php

declare(strict_types=1);

namespace App\Provider\CMS\ProductOptionColor;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Repository\Product\ProductOptionValueRepository;

class ProductOptionColorProvider
{
    private const FINISH_CODES = [
        'finish_chene_naturel',
        'finish_noyer_naturel',
    ];
    private ProductOptionValueRepository $productOptionValueRepository;
    private static array $colors = [];

    public function __construct(ProductOptionValueRepository $productOptionValueRepository)
    {
        $this->productOptionValueRepository = $productOptionValueRepository;
    }

    /**
     * @return array|ProductOptionValue[]
     */
    public function getColorsCodes(bool $cache = false): array
    {
        if ($cache && static::$colors) {
            return static::$colors;
        }
        $colors = $this->productOptionValueRepository->getEnabledColors();
        $naturalFinishes = $this->productOptionValueRepository->findByOptionAndCodes(ProductOption::PRODUCT_OPTION_FINISH, self::FINISH_CODES);
        array_push($colors, ...$naturalFinishes);
        
        $colorsCode = [];
        foreach ($colors as $color) {
            $code = $color->getCode();
            if (!in_array($code, ProductOptionValue::HIDDEN_COLORS, true)) {
                $colorsCode[] = $color;
            }
        }

        if ($cache) {
            static::$colors = $colorsCode;
        }

        return $colorsCode;
    }
}
