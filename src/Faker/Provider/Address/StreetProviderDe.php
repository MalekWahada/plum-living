<?php

declare(strict_types=1);

namespace App\Faker\Provider\Address;

use Faker\Provider\Base as BaseProvider;

final class StreetProviderDe extends BaseProvider
{
    private const STREET_PROVIDER_DE = [
        'Karl-Ernst-Metzger-Straße 70',
        'Dimitri-Esser-Gasse 56b',
        'Anna-Schaller-Straße 49a',
        'Gieseplatz 2c',
        'Veit-Bernhardt-Weg 2a',
        'Osman-Runge-Ring 321',
        'Dorit-Walther-Gasse 79a',
        'Neumannplatz 1/4',
        'Denise-Will-Ring 57c',
        'Kesslerallee 6a',
        'Manuel-Lange-Platz 39',
        'Bachmannstraße 9',
        'Eckard-Seidel-Straße 1a',
        'Wunderlichplatz 1b',
    ];


    public function streetProviderDe(): ?string
    {
        return self::randomElement(self::STREET_PROVIDER_DE);
    }
}
