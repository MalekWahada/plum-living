<?php

declare(strict_types=1);

namespace App\Faker\Provider\Customer;

use Faker\Provider\Base as BaseProvider;

final class CustomerTypeProvider extends BaseProvider
{
    private const CUSTOMER_TYPE_PROVIDER = [
        'particular_customer',
        'interior_designer',
        'renovation_agency',
        'other_professional',
        null
    ];


    public function customerType(): ?string
    {
        return self::randomElement(self::CUSTOMER_TYPE_PROVIDER);
    }
}
