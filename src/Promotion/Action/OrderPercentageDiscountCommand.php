<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Promotion\Applicator\FixedDiscountPromotionAdjustmentApplicator;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Promotion\Action\DiscountPromotionActionCommand;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

final class OrderPercentageDiscountCommand extends DiscountPromotionActionCommand
{
    public const TYPE = 'order_percentage_discount';

    private FixedDiscountPromotionAdjustmentApplicator $fixedDiscountPromotionAdjustmentApplicator;

    public function __construct(FixedDiscountPromotionAdjustmentApplicator $fixedDiscountPromotionAdjustmentApplicator)
    {
        $this->fixedDiscountPromotionAdjustmentApplicator = $fixedDiscountPromotionAdjustmentApplicator;
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        /** @var OrderInterface $subject */
        Assert::isInstanceOf($subject, OrderInterface::class);

        if (!$this->isSubjectValid($subject)) {
            return false;
        }

        try {
            $this->isConfigurationValid($configuration);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        $promotionAmount = $this->calculateAdjustmentAmount($subject->getPromotionSubjectTotal(), $configuration['percentage']);
        if (0 === $promotionAmount) {
            return false;
        }

        $this->fixedDiscountPromotionAdjustmentApplicator->apply($subject, $promotion, $promotionAmount);

        return true;
    }

    protected function isConfigurationValid(array $configuration): void
    {
        Assert::keyExists($configuration, 'percentage');
        Assert::greaterThan($configuration['percentage'], 0);
        Assert::lessThanEq($configuration['percentage'], 1);
    }

    private function calculateAdjustmentAmount(int $promotionSubjectTotal, float $percentage): int
    {
        return -1 * (int) round($promotionSubjectTotal * $percentage);
    }
}
