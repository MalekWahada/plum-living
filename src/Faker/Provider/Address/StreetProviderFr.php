<?php

declare(strict_types=1);

namespace App\Faker\Provider\Address;

use Faker\Provider\Base as BaseProvider;

final class StreetProviderFr extends BaseProvider
{
    private const STREET_PROVIDER_FR = [
        '10 avenue du général de gaulle',
        '10 avenue de verdun',
        '10 avenue de la grande armée',
        '9 rue de turin',
        '9 rue de laborde',
        '11 rue cambronne',
        '11 boulevard saint martin',
        '12 rue du faubourg saint-martin',
        '12 rue de chaligny',
        '13 boulevard de strasbourg',
        '13 boulevard du temple',
        '13 boulevard du montparnasse',
        '14 rue des écoles',
        '14 rue de la paix',
    ];


    public function streetProviderFr(): ?string
    {
        return self::randomElement(self::STREET_PROVIDER_FR);
    }
}
