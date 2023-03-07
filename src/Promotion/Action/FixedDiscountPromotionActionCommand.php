<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Promotion\Applicator\FixedDiscountPromotionAdjustmentApplicator;
use InvalidArgumentException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Promotion\Action\DiscountPromotionActionCommand;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

final class FixedDiscountPromotionActionCommand extends DiscountPromotionActionCommand
{
    public const TYPE = 'order_fixed_discount';

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

        $channelCode = $subject->getChannel()->getCode();
        if (!isset($configuration[$channelCode])) {
            return false;
        }

        try {
            $this->isConfigurationValid($configuration[$channelCode]);
        } catch (InvalidArgumentException $exception) {
            return false;
        }

        $promotionAmount = $this->calculateOrderPromotionAdjustment(
            $subject->getPromotionSubjectTotal(),
            $configuration[$channelCode]['amount']
        );
        if (0 === $promotionAmount) {
            return false;
        }

        $this->fixedDiscountPromotionAdjustmentApplicator->apply($subject, $promotion, $promotionAmount);

        return true;
    }

    protected function isConfigurationValid(array $configuration): void
    {
        Assert::keyExists($configuration, 'amount');
        Assert::integer($configuration['amount']);
    }

    private function calculateOrderPromotionAdjustment(int $promotionSubjectTotal, int $targetPromotionAmount): int
    {
        return -1 * min($promotionSubjectTotal, $targetPromotionAmount);
    }
}
