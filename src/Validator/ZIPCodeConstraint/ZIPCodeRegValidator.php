<?php

declare(strict_types=1);

namespace App\Validator\ZIPCodeConstraint;

use App\Entity\Addressing\Address;
use App\Provider\Address\ZIPCodeProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ZIPCodeRegValidator extends ConstraintValidator
{
    private ZIPCodeProvider $ZIPCodeProvider;

    public function __construct(ZIPCodeProvider $ZIPCodeProvider)
    {
        $this->ZIPCodeProvider = $ZIPCodeProvider;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ZIPCodeReg) {
            throw new UnexpectedTypeException($constraint, ZIPCodeReg::class);
        }

        if ($value === null || !$value instanceof Address) {
            return;
        }

        $countryCode = $value->getCountryCode();
        $pattern = $this->ZIPCodeProvider->getPattern($countryCode);
        $pattern = '`' . $pattern . '`';

        if (!preg_match($pattern, (string) $value->getPostcode())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value->getPostcode())
                ->atPath('postcode')
                ->addViolation()
            ;
        }
    }
}
