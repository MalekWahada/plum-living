<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use App\Entity\Order\Order;
use App\Entity\Shipping\ShippingCategory;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

/**
 * All items in order must have sample shipping category for this rule
 */
class ContainsOnlySampleShippingCategoryPromotionRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'contains_only_sample_shipping_category_promotion';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof Order) {
            throw new UnsupportedTypeException($subject, Order::class);
        }

        foreach ($subject->getItems() as $item) {
            $shippingCategory = (null !== $item->getVariant()) ? $item->getVariant()->getShippingCategory() : null;

            if (null === $shippingCategory
                || $shippingCategory->getCode() !== ShippingCategory::SAMPLE_CODE) {
                return false; // Promotion won't apply
            }
        }
        return true;
    }
}
