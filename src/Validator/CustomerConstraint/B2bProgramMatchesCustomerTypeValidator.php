<?php

declare(strict_types=1);

namespace App\Validator\CustomerConstraint;

use App\Entity\Customer\Customer;
use App\Provider\Customer\CustomerTypeChoicesProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class B2bProgramMatchesCustomerTypeValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Customer) {
            return;
        }

        if ($value->hasB2BProgram()
            && !\in_array($value->getCustomerType(), CustomerTypeChoicesProvider::CHOICES_B2B, true)) {
            $this->context->buildViolation('app.b2b_program.cannot_use_for_b2c_type')
                ->setParameter(
                    '%customerType%',
                    strtolower($this->translator->trans(
                        sprintf('app.form.customer.customer_type.choices.%s', $value->getCustomerType())
                    ))
                )
                ->atPath('customerType')
                ->addViolation();
        }
    }
}
