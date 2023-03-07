<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\ProductConstraint;

use App\Entity\Customer\Customer;
use App\Validator\CustomerConstraint\B2bProgramMatchesCustomerType;
use App\Validator\CustomerConstraint\B2bProgramMatchesCustomerTypeValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \App\Validator\ProductConstraint\B2bProgramMatchesCustomerTypeValidator
 */
final class B2bProgramMatchesCustomerTypeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): B2bProgramMatchesCustomerTypeValidator
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static fn(string $arg): string => $arg);

        return new B2bProgramMatchesCustomerTypeValidator($translator);
    }

    public function testNonCustomerAreIgnored(): void
    {
        $this->validator->validate(new \stdClass(), new B2bProgramMatchesCustomerType());

        $this->assertNoViolation();
    }

    public function testB2cCustomersAreValid(): void
    {
        $customer = new class extends Customer {
            public function hasB2BProgram(): bool
            {
                return false;
            }

            public function getCustomerType(): ?string
            {
                return 'any_non_pro_type';
            }
        };

        $this->validator->validate($customer, new B2bProgramMatchesCustomerType());

        $this->assertNoViolation();
    }

    public function testB2bCustomersWithIncorrectTypeAreInvalid(): void
    {
        $customer = new class extends Customer {
            public function hasB2BProgram(): bool
            {
                return true;
            }

            public function getCustomerType(): ?string
            {
                return 'any_non_pro_type';
            }
        };

        $this->validator->validate($customer, new B2bProgramMatchesCustomerType());

        $this->buildViolation('app.b2b_program.cannot_use_for_b2c_type')
            ->setParameter('%customerType%', 'app.form.customer.customer_type.choices.any_non_pro_type')
            ->atPath('property.path.customerType')
            ->assertRaised();
    }
}
