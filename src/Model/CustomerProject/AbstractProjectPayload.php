<?php

declare(strict_types=1);

namespace App\Model\CustomerProject;

use App\Model\AbstractValidatableModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractProjectPayload extends AbstractValidatableModel
{
    protected array $data;
    protected function getDataValidationConstraint(): ?Constraint
    {
        return null;
    }

    /**
     * Custom validation for data depending on the object state
     * @param ValidatorInterface $validator
     * @param Constraint|Constraint[] $constraints
     * @param string|GroupSequence|array<string|GroupSequence>|null $groups
     * @return ConstraintViolationListInterface
     */
    public function validate(ValidatorInterface $validator, $constraints = null, $groups = null): ConstraintViolationListInterface
    {
        $violations = $validator->validate($this->data, $this->getDataValidationConstraint(), $groups); // Validate using custom constraints
        $violations->addAll($validator->validateProperty($this, 'data', $groups)); // Validate using the default constraints
        if (null !== $constraints) {
            $violations->addAll($validator->validate($this->data, $constraints, $groups));
        }
        return $violations;
    }
}
