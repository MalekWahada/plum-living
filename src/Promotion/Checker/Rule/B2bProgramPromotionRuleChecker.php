<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use App\Entity\Promotion\PromotionCoupon;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionCouponAwarePromotionSubjectInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class B2bProgramPromotionRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'b2b_program_promotion';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof PromotionCouponAwarePromotionSubjectInterface) {
            return false;
        }

        $coupon = $subject->getPromotionCoupon();
        if (!$coupon instanceof PromotionCoupon) {
            return false;
        }

        if (!$coupon->getCustomer()) {
            return false;
        }

        return $coupon->getCustomer()->hasB2BProgram();
    }
}
