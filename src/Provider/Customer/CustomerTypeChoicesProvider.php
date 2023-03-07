<?php

declare(strict_types=1);

namespace App\Provider\Customer;

class CustomerTypeChoicesProvider
{
    public const PARTICULAR_CUSTOMER = 'particular_customer';
    public const INTERIOR_DESIGNER = 'interior_designer';
    public const RENOVATION_AGENCY = 'renovation_agency';
    public const OTHER_PROFESSIONAL = 'other_professional';
    public const COMPANY_ALL_TRADES = 'company_all_trades';
    public const REAL_ESTATE_DEVELOPER = 'real_estate_developer';
    public const OTHER_RENOVATION_AGENCY = 'other_renovation_agency';

    private const CHOICES_PRIVATE_OR_B2B = [
        self::PARTICULAR_CUSTOMER,
        self::INTERIOR_DESIGNER,
        self::COMPANY_ALL_TRADES,
        self::REAL_ESTATE_DEVELOPER,
        self::OTHER_RENOVATION_AGENCY,
        self::OTHER_PROFESSIONAL,
    ];

    public const CHOICES_B2B = [
        self::INTERIOR_DESIGNER,
        self::COMPANY_ALL_TRADES,
        self::REAL_ESTATE_DEVELOPER,
        self::OTHER_RENOVATION_AGENCY,
    ];

    public function getChoices() :array
    {
        return self::CHOICES_PRIVATE_OR_B2B;
    }
}
