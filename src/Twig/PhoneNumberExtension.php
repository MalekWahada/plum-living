<?php

declare(strict_types=1);

namespace App\Twig;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Webmozart\Assert\Assert;

class PhoneNumberExtension extends AbstractExtension
{
    private PhoneNumberUtil $phoneNumberUtil;
    const DEFAULT_REGION = "FR";

    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('phone_number_parse', [$this, 'parsePhoneNumber']),
            new TwigFilter('phone_number_region_code', [$this, 'getRegionCodeForNumber']),
        ];
    }

    public function parsePhoneNumber(?string $number, string $defaultRegionCode = self::DEFAULT_REGION): PhoneNumber
    {
        try {
            return $this->phoneNumberUtil->parse($number, $defaultRegionCode);
        } catch (NumberParseException $e) { // Unable to parse the number. Consider it as a FRENCH number to avoid data loss
            $code = $this->phoneNumberUtil->getCountryCodeForRegion($defaultRegionCode);
            return (new PhoneNumber())->setRawInput($number)->setCountryCode($code);
        }
    }

    public function getRegionCodeForNumber(PhoneNumber $phoneNumber): string
    {
        Assert::isInstanceOf($phoneNumber, PhoneNumber::class);

        return $this->phoneNumberUtil->getRegionCodeForCountryCode($phoneNumber->getCountryCode());
    }
}
