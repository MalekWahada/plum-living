<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidatableModel implements ValidatableModel
{
    public function validate(ValidatorInterface $validator, $constraints = null, $groups = null): ConstraintViolationListInterface
    {
        return $validator->validate($this); // Use default class validation constraints
    }

    /**
     * Wrap constraint array in {@link Assert\Optional} if optional true.
     * @param bool $isOptional
     * @param array $constraint
     * @return Constraint
     */
    protected static function getConstraint(bool $isOptional, array $constraint): Constraint
    {
        return $isOptional ? new Assert\Optional($constraint) : new Assert\Required($constraint);
    }
}
