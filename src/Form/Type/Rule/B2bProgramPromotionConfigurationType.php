<?php

declare(strict_types=1);

namespace App\Form\Type\Rule;

use Symfony\Component\Form\AbstractType;

class B2bProgramPromotionConfigurationType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'app_promotion_rule_b2b_configuration';
    }
}
