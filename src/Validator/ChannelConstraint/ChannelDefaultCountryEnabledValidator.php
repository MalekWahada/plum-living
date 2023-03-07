<?php

declare(strict_types=1);

namespace App\Validator\ChannelConstraint;

use App\Entity\Channel\Channel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ChannelDefaultCountryEnabledValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var ChannelDefaultCountryEnabled $constraint */
        Assert::isInstanceOf($constraint, ChannelDefaultCountryEnabled::class);

        /** @var Channel $value */
        Assert::isInstanceOf($value, Channel::class);

        $defaultCountry = $value->getDefaultCountry();
        if ($defaultCountry !== null && !$value->hasCountry($defaultCountry)) {
            $this->context->buildViolation($constraint->message)->atPath('defaultCountry')->addViolation();
        }
    }
}
