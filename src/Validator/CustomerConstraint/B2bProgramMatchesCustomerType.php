<?php

declare(strict_types=1);

namespace App\Validator\CustomerConstraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class B2bProgramMatchesCustomerType extends Constraint
{
    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
