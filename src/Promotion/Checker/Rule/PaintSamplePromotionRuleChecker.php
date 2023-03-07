<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use App\Entity\Order\Order;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class PaintSamplePromotionRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'paint_sample_promotion';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof Order) {
            throw new UnsupportedTypeException($subject, Order::class);
        }

        if (!isset($configuration['customerId']) || !is_int($configuration['customerId'])) {
            return false;
        }

        /** @var CustomerInterface|null $customer */
        $customer = $subject->getCustomer();
        if (null === $customer) {
            return false;
        }
        return ($customer->getId() === $configuration['customerId']) && $subject->hasPaintItem();
    }
}
