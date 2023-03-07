<?php

declare(strict_types=1);

namespace App\Promotion\Remover;

use App\Entity\Order\Order;
use App\Promotion\Checker\Rule\FrontSamplePromotionRuleChecker;
use App\Promotion\Checker\Rule\PaintSamplePromotionRuleChecker;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;

class PromotionSampleRemover
{
    // In order to keep the order sample promotion valid we have to decrement the usage
    public function decrementPromotionUsage(Order $order): void
    {
        /** @var PromotionInterface $promotion */
        foreach ($order->getPromotions() as $promotion) {
            /** @var PromotionRuleInterface $rule */
            foreach ($promotion->getRules() as $rule) {
                if (($rule->getType() === FrontSamplePromotionRuleChecker::TYPE || $rule->getType() === PaintSamplePromotionRuleChecker::TYPE) && $promotion->getUsed() > 0) {
                    $promotion->decrementUsed();
                    break;
                }
            }
        }
    }
}
