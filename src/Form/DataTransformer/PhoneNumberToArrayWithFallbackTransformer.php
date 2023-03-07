<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Form\DataTransformer\PhoneNumberToArrayTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberToArrayWithFallbackTransformer extends PhoneNumberToArrayTransformer
{
    private array $countryChoices;
    private string $defaultRegion;

    public function __construct(array $countryChoices, string $defaultRegion)
    {
        parent::__construct($countryChoices);
        $this->countryChoices = $countryChoices;
        $this->defaultRegion = $defaultRegion;
    }

    /**
     * @param mixed|null $phoneNumber
     * @return array<string, string|null>
     */
    public function transform($phoneNumber): array
    {
        if (null === $phoneNumber) {
            return ['country' => '', 'number' => ''];
        } elseif (false === $phoneNumber instanceof PhoneNumber) { /** @phpstan-ignore-line */
            throw new TransformationFailedException('Expected a \libphonenumber\PhoneNumber.');
        }

        $util = PhoneNumberUtil::getInstance();
        $phoneNumberRegion = $util->getRegionCodeForNumber($phoneNumber);

        if (false === \in_array($phoneNumberRegion, $this->countryChoices)) {
            $phoneNumberRegion = $this->defaultRegion;
        }

        return [
            'country' => $phoneNumberRegion,
            'number' => $util->format($phoneNumber, PhoneNumberFormat::NATIONAL),
        ];
    }

    /**
     * @param array $value
     * @return PhoneNumber|null
     */
    public function reverseTransform($value): ?PhoneNumber
    {
        try {
            return parent::reverseTransform($value);
        } catch (TransformationFailedException $e) {
            $util = PhoneNumberUtil::getInstance();

            $code = $util->getCountryCodeForRegion($this->defaultRegion);
            return (new PhoneNumber())->setRawInput($value['number'])->setCountryCode($code);
        }
    }
}
