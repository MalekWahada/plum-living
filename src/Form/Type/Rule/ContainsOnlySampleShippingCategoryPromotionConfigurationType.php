<?php

declare(strict_types=1);

namespace App\Form\Type\Rule;

use Symfony\Component\Form\AbstractType;

class ContainsOnlySampleShippingCategoryPromotionConfigurationType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'app_contains_only_sample_shipping_category_promotion_rule_configuration';
    }
}
