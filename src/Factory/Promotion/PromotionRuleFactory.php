<?php

declare(strict_types=1);

namespace App\Factory\Promotion;

use App\Promotion\Checker\Rule\PaintSamplePromotionRuleChecker;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Sylius\Component\Core\Factory\PromotionRuleFactoryInterface as BasePromotionRuleFactoryInterface;

final class PromotionRuleFactory implements PromotionRuleFactoryInterface
{
    private BasePromotionRuleFactoryInterface $decoratedFactory;

    public function __construct(BasePromotionRuleFactoryInterface $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    public function createNew(): PromotionRuleInterface
    {
        $promotionRule = $this->decoratedFactory->createNew();
        \assert($promotionRule instanceof PromotionRuleInterface);
        
        return $promotionRule;
    }

    public function createCartQuantity(int $count): PromotionRuleInterface
    {
        return $this->decoratedFactory->createCartQuantity($count);
    }

    public function createItemTotal(string $channelCode, int $amount): PromotionRuleInterface
    {
        return $this->decoratedFactory->createItemTotal($channelCode, $amount);
    }

    public function createHasTaxon(array $taxons): PromotionRuleInterface
    {
        return $this->decoratedFactory->createHasTaxon($taxons);
    }

    public function createItemsFromTaxonTotal(string $channelCode, string $taxonCode, int $amount): PromotionRuleInterface
    {
        return $this->decoratedFactory->createItemsFromTaxonTotal($channelCode, $taxonCode, $amount);
    }

    public function createNthOrder(int $nth): PromotionRuleInterface
    {
        return $this->decoratedFactory->createNthOrder($nth);
    }

    public function createContainsProduct(string $productCode): PromotionRuleInterface
    {
        return $this->decoratedFactory->createContainsProduct($productCode);
    }

    public function createSamplesRule(CustomerInterface $customer, string $type): PromotionRuleInterface
    {
        /** @var PromotionRuleInterface $rule */
        $rule = $this->createNew();
        $rule->setType($type);
        $rule->setConfiguration(['customerId' => $customer->getId()]);

        return $rule;
    }
}
