<?php

declare(strict_types=1);

namespace App\Promotion\Applicator;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\AdjustableInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;

class FixedDiscountPromotionAdjustmentApplicator
{
    private AdjustmentFactoryInterface $adjustmentFactory;

    public function __construct(AdjustmentFactoryInterface $adjustmentFactory)
    {
        $this->adjustmentFactory = $adjustmentFactory;
    }

    public function apply(AdjustableInterface $order, PromotionInterface $promotion, int $amount): void
    {
        $adjustment = $this->adjustmentFactory->createWithData(
            AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT,
            $promotion->getName(),
            $amount
        );
        $adjustment->setOriginCode($promotion->getCode());
        $order->addAdjustment($adjustment);
    }
}
