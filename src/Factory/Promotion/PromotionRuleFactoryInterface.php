<?php

declare(strict_types=1);

namespace App\Factory\Promotion;

use Sylius\Component\Core\Factory\PromotionRuleFactoryInterface as BasePromotionRuleFactoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;

interface PromotionRuleFactoryInterface extends BasePromotionRuleFactoryInterface
{
    public function createSamplesRule(CustomerInterface $customer, string $type): PromotionRuleInterface;
}
