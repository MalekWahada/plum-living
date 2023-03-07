<?php

declare(strict_types=1);

namespace App\Validator\ProductConstraint;

use Symfony\Component\Validator\Constraint;

class ProductVariantExists extends Constraint
{
    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
