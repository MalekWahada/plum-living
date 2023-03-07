<?php

declare(strict_types=1);

namespace App\Validator\ZIPCodeConstraint;

use Symfony\Component\Validator\Constraint;

class ZIPCodeReg extends Constraint
{
    public string $message = 'app.form.address.postCode.format';

    public function getTargets(): array
    {
        return [
            self::CLASS_CONSTRAINT,
        ];
    }
}
