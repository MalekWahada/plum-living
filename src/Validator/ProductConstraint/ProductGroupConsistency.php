<?php

declare(strict_types=1);

namespace App\Validator\ProductConstraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ProductGroupConsistency extends Constraint
{
    public string $message = 'app.product_group.consistency_error';

    public function getTargets(): array
    {
        return [
            self::CLASS_CONSTRAINT,
            self::PROPERTY_CONSTRAINT
        ];
    }
}
