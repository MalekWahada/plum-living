<?php

declare(strict_types=1);

namespace App\Factory\Promotion;

use App\Entity\Promotion\Promotion;
use App\Promotion\Action\OrderPercentageOfFrontItemsDiscountCommand;
use App\Promotion\Checker\Rule\B2bProgramPromotionRuleChecker;
use App\Repository\Promotion\PromotionRepositoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Factory\PromotionActionFactoryInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Core\Promotion\Action\UnitPercentageDiscountPromotionActionCommand;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class PromotionFactory implements PromotionFactoryInterface
{
    private FactoryInterface $decoratedPromotionFactory;
    private PromotionRepositoryInterface $promotionRepository;
    private PromotionRuleFactoryInterface $promotionRuleFactory;
    private PromotionActionFactoryInterface $promotionActionFactory;
    private ChannelRepositoryInterface $channelRepository;

    public function __construct(
        FactoryInterface $decoratedPromotionFactory,
        PromotionRepositoryInterface $promotionRepository,
        PromotionRuleFactoryInterface $promotionRuleFactory,
        PromotionActionFactoryInterface $promotionActionFactory,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->decoratedPromotionFactory = $decoratedPromotionFactory;
        $this->promotionRepository = $promotionRepository;
        $this->promotionRuleFactory = $promotionRuleFactory;
        $this->promotionActionFactory = $promotionActionFactory;
        $this->channelRepository = $channelRepository;
    }

    public function createNew(): PromotionInterface
    {
        /** @var PromotionInterface $promotion */
        $promotion = $this->decoratedPromotionFactory->createNew();

        return $promotion;
    }

    public function createForB2bProgram(): PromotionInterface
    {
        /** @var PromotionInterface $promotion */
        $promotion = $this->decoratedPromotionFactory->createNew();
        $promotion->setCode(Promotion::B2B_PROGRAM_PROMOTION_CODE);
        $promotion->setCouponBased(true);
        $promotion->setExclusive(true);
        $promotion->setName(ucfirst(str_replace('_', ' ', Promotion::B2B_PROGRAM_PROMOTION_CODE)));

        /** @var PromotionRuleInterface $rule */
        $rule = $this->promotionRuleFactory->createNew();
        $rule->setType(B2bProgramPromotionRuleChecker::TYPE);
        $promotion->addRule($rule);

        /** @var PromotionActionInterface $action */
        $action = $this->promotionActionFactory->createNew();
        $action->setType(OrderPercentageOfFrontItemsDiscountCommand::TYPE);
        $action->setConfiguration([
            'percentage' => 10,
        ]);
        $promotion->addAction($action);

        // Set promotion available for all channels
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $promotion->addChannel($channel);
        }

        return $promotion;
    }
}
