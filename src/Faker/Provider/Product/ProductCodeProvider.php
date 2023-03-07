<?php

declare(strict_types=1);

namespace App\Faker\Provider\Product;

use Faker\Provider\Base as BaseProvider;

final class ProductCodeProvider extends BaseProvider
{
    private const PRODUCT_CODE_PROVIDER = [
        '2500P-S25',
        '2500P-S24',
        '2500P-S23',
        '2500P-S22',
        '2500P-S21',
        '2500P-S20',
        '2500P-S19',
        '2500P-S18',
        '2500P-S17',
        '2500P-S16',
        '2500P-S15',
        '2500P-S14',
        '2500P-S13',
        '2500P-S12',
        '2500P-S11',
        '2500P-S10',
        '2500P-S09',
        '2500P-S08',
        '2500P-S07',
        '2500P-S06',
        '2500P-S05',
        '2500P-S04',
        '2500P-S03',
        '2500P-S02',
        '2500P-S01',
    ];

    public function productCode(): ?string
    {
        return self::randomElement(self::PRODUCT_CODE_PROVIDER);
    }
}
