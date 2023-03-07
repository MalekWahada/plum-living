<?php

declare(strict_types=1);

namespace App\Faker\Provider\Address;

use Faker\Provider\Base as BaseProvider;

final class ProvinceCodeProviderFr extends BaseProvider
{
    private const PROVINCE_CODE_PROVIDER = [
        'FR-92',
        'FR-93',
        'FR-94',
        'FR-95',
        'FR-75',
        'FR-77',
        'FR-78',
        'FR-33'
    ];


    public function provinceCodeFr(): ?string
    {
        return self::randomElement(self::PROVINCE_CODE_PROVIDER);
    }
}
