<?php

declare(strict_types=1);

namespace App\Promotion\Applicator;

use App\Promotion\Resolver\AfterTaxesPromotionResolver;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Action\PromotionApplicatorInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class PromotionApplicator implements PromotionApplicatorInterface
{
    private ServiceRegistryInterface $registry;

    private AfterTaxesPromotionResolver $afterTaxesPromotionResolver;

    public function __construct(
        ServiceRegistryInterface $registry,
        AfterTaxesPromotionResolver $afterTaxesPromotionResolver
    ) {
        $this->registry = $registry;
        $this->afterTaxesPromotionResolver = $afterTaxesPromotionResolver;
    }

    public function apply(PromotionSubjectInterface $subject, PromotionInterface $promotion): void
    {
        $applyPromotion = false;
        foreach ($promotion->getActions() as $action) {
            $command = $this->getActionCommandByType($action->getType());
            if ($this->afterTaxesPromotionResolver->isAfterTax($command)) {
                continue;
            }
            $result = $command->execute($subject, $action->getConfiguration(), $promotion);
            $applyPromotion = $applyPromotion || $result;
        }

        if ($applyPromotion) {
            $subject->addPromotion($promotion);
        }
    }

    public function revert(PromotionSubjectInterface $subject, PromotionInterface $promotion): void
    {
        foreach ($promotion->getActions() as $action) {
            $command = $this->getActionCommandByType($action->getType());
            if ($this->afterTaxesPromotionResolver->isAfterTax($command)) {
                continue;
            }
            $command->revert($subject, $action->getConfiguration(), $promotion);
        }

        $subject->removePromotion($promotion);
    }

    private function getActionCommandByType(string $type): PromotionActionCommandInterface
    {
        $command = $this->registry->get($type);
        \assert($command instanceof PromotionActionCommandInterface);
        
        return $command;
    }
}
