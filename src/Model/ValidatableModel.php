<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

interface ValidatableModel
{
    /**
     * Validates the value against his internal list of constraints.
     *
     * If no constraint is passed, the constraint of the class are used.
     *
     * @param ValidatorInterface $validator
     * @return ConstraintViolationListInterface A list of constraint violations
     * @param Constraint|Constraint[]                               $constraints The constraint(s) to validate against
     * @param string|GroupSequence|array<string|GroupSequence>|null $groups      The validation groups to validate. If none is given, "Default" is assumed
     *                                          If the list is empty, validation
     *                                          succeeded
     */
    public function validate(ValidatorInterface $validator, $constraints = null, $groups = null): ConstraintViolationListInterface;
}
