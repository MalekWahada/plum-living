<?php

declare(strict_types=1);

namespace App\Faker\Provider\Address;

use Faker\Provider\Base as BaseProvider;

final class ProvinceProviderDe extends BaseProvider
{
    private const PROVINCE_PROVIDER = [
        'Autre',
        'Bade-Wurtemberg',
        'Basse-Saxe',
        'Bavière',
        'Berlin',
        'Brandebourg',
        'Brême',
        'Hambourg',
        'Hesse',
        'Mecklembourg-Poméranie-Occidentale',
        'Rhénanie-du-Nord-Westphalie',
        'Rhénanie-Palatinat',
        'Sarre',
        'Saxe',
        'Saxe-Anhalt',
        'Schleswig-Holstein',
        'Thuringe'
    ];

    public function provinceDe(int $current): string
    {
        return sprintf('DE-%02d', $current);
    }

    public function provinceNameDe(int $current): string
    {
        return self::PROVINCE_PROVIDER[$current];
    }
}
