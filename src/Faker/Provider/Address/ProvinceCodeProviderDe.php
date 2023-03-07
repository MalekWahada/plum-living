<?php

declare(strict_types=1);

namespace App\Faker\Provider\Address;

use Faker\Provider\Base as BaseProvider;

final class ProvinceCodeProviderDe extends BaseProvider
{
    private const PROVINCE_CODE_PROVIDER = [
        'DE-92',
        'DE-93',
        'DE-94',
        'DE-95',
        'DE-75',
        'DE-77',
        'DE-78',
        'DE-39'
    ];


    public function provinceCodeDe(): ?string
    {
        return self::randomElement(self::PROVINCE_CODE_PROVIDER);
    }
}
