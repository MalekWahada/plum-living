<?php

declare(strict_types=1);

namespace App\Promotion\Generator;

use App\Entity\Channel\Channel;
use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Entity\Promotion\Promotion;
use App\Entity\Taxonomy\Taxon;
use App\Factory\Promotion\PromotionRuleFactory;
use App\Factory\Promotion\PromotionRuleFactoryInterface;
use App\Promotion\Checker\Rule\FrontSamplePromotionRuleChecker;
use App\Promotion\Checker\Rule\PaintSamplePromotionRuleChecker;
use Sylius\Component\Core\Factory\PromotionActionFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Repository\PromotionRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class PromotionSampleGenerator
{
    private FactoryInterface $promotionFactory;

    private PromotionRuleFactoryInterface $ruleFactory;

    private PromotionActionFactoryInterface $actionFactory;

    private PromotionRepositoryInterface $promotionRepository;

    public function __construct(
        FactoryInterface $promotionFactory,
        PromotionRuleFactoryInterface $ruleFactory,
        PromotionActionFactoryInterface $actionFactory,
        PromotionRepositoryInterface $promotionRepository
    ) {
        $this->promotionFactory = $promotionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->actionFactory = $actionFactory;
        $this->promotionRepository = $promotionRepository;
    }

    public function generateSamplePromotionForUser(Order $order): void
    {
        if ($order->getTotal() <= 0) {
            return;
        }

        /** @var Customer $customer */
        $customer = $order->getCustomer();

        /** @var Channel $channel */
        $channel = $order->getChannel();

        if (null !== $customer) {
            if ($order->hasFacadeSampleItem()) {
                $amount = $order->getItemTypeTotalAmount(Taxon::TAXON_SAMPLE_FRONT_CODE);
                if ($amount > 0) {
                    $samplePromotion = $this->createPromotionForType(FrontSamplePromotionRuleChecker::TYPE, $amount, $order, $customer, $channel);
                    $this->promotionRepository->add($samplePromotion);
                }
            }
            if ($order->hasPaintSampleItem()) {
                $amount = $order->getItemTypeTotalAmount(Taxon::TAXON_SAMPLE_PAINT_CODE);
                if ($amount > 0) {
                    $samplePromotion = $this->createPromotionForType(PaintSamplePromotionRuleChecker::TYPE, $amount, $order, $customer, $channel);
                    $this->promotionRepository->add($samplePromotion);
                }
            }
        }
    }

    private function createPromotionForType(string $promotionType, int $amount, OrderInterface $order, CustomerInterface $customer, ChannelInterface $channel): Promotion
    {
        /** @var Promotion $samplePromotion */
        $samplePromotion = $this->promotionFactory->createNew();

        $code = str_replace("_", "-", $promotionType);
        $name = ucwords(str_replace("_", " ", $promotionType));
        $samplePromotion->setCode(sprintf('%s-O%d-C%d', $code, $order->getId(), $customer->getId()));
        $samplePromotion->setName(sprintf('%s O%d C%d', $name, $order->getId(), $customer->getId()));

        $samplePromotion->setUsageLimit(1);
        $samplePromotion->addChannel($channel);

        $samplePromotion->addRule($this->ruleFactory->createSamplesRule($customer, $promotionType)); //add new Front Sample Promotion rule

        // add new fixed promotion action with amount equal to the sum of the samples price (With taxes)
        $action = $this->actionFactory->createFixedDiscount($amount, $channel->getCode());
        $samplePromotion->addAction($action);

        return  $samplePromotion;
    }
}
