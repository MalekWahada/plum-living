<?php

declare(strict_types=1);

namespace App\Promotion\Processor;

use App\Promotion\Applicator\AfterTaxesPromotionApplicator;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionEligibilityCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Promotion\Processor\PromotionProcessorInterface;
use Sylius\Component\Promotion\Provider\PreQualifiedPromotionsProviderInterface;

final class AfterTaxesPromotionProcessor implements PromotionProcessorInterface
{
    private PreQualifiedPromotionsProviderInterface $preQualifiedPromotionsProvider;

    private PromotionEligibilityCheckerInterface $promotionEligibilityChecker;

    private AfterTaxesPromotionApplicator $promotionApplicator;

    public function __construct(
        PreQualifiedPromotionsProviderInterface $preQualifiedPromotionsProvider,
        PromotionEligibilityCheckerInterface $promotionEligibilityChecker,
        AfterTaxesPromotionApplicator $promotionApplicator
    ) {
        $this->preQualifiedPromotionsProvider = $preQualifiedPromotionsProvider;
        $this->promotionEligibilityChecker = $promotionEligibilityChecker;
        $this->promotionApplicator = $promotionApplicator;
    }

    public function process(PromotionSubjectInterface $subject): void
    {
        foreach ($subject->getPromotions() as $promotion) {
            $this->promotionApplicator->revert($subject, $promotion);
        }

        $preQualifiedPromotions = $this->preQualifiedPromotionsProvider->getPromotions($subject);

        foreach ($preQualifiedPromotions as $promotion) {
            if ($promotion->isExclusive() && $this->promotionEligibilityChecker->isEligible($subject, $promotion)) {
                $this->promotionApplicator->apply($subject, $promotion);

                return;
            }
        }

        foreach ($preQualifiedPromotions as $promotion) {
            if (!$promotion->isExclusive() && $this->promotionEligibilityChecker->isEligible($subject, $promotion)) {
                $this->promotionApplicator->apply($subject, $promotion);
            }
        }
    }
}
