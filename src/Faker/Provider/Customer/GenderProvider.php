<?php

declare(strict_types=1);

namespace App\Faker\Provider\Customer;

use Faker\Provider\Base as BaseProvider;

final class GenderProvider extends BaseProvider
{
    private const GENDER_PROVIDER = [
        'm',
        'f',
        'u',
    ];


    public function gender(): string
    {
        return self::randomElement(self::GENDER_PROVIDER);
    }
}
