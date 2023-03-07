<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Entity\Order\OrderItemUnit;
use App\Entity\Product\Product;
use App\Entity\Taxonomy\Taxon;
use App\Promotion\Applicator\FixedDiscountPromotionAdjustmentApplicator;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Promotion\Action\DiscountPromotionActionCommand;
use Sylius\Component\Core\Promotion\Action\UnitDiscountPromotionActionCommand;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class OrderPercentageOfFrontItemsDiscountCommand extends DiscountPromotionActionCommand
{
    public const TYPE = 'order_percentage_of_front_items_discount';

    private FixedDiscountPromotionAdjustmentApplicator $fixedDiscountPromotionAdjustmentApplicator;

    public function __construct(FixedDiscountPromotionAdjustmentApplicator $fixedDiscountPromotionAdjustmentApplicator)
    {
        $this->fixedDiscountPromotionAdjustmentApplicator = $fixedDiscountPromotionAdjustmentApplicator;
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        if (!$subject instanceof OrderInterface) {
            return false;
        }

        $promotionAmount = 0;
        $otherProductsTotal = 0;

        foreach ($subject->getItems() as $item) {
            if (!$product = $item->getProduct()) {
                continue;
            }

            \assert($product instanceof Product);

            if ($product->isType(Taxon::TAXON_SAMPLE_CODE)) { // Samples are processed by another action
                continue;
            }

            if ($product->isFacade()) {
                $promotionAmount += (int) floor($item->getTotal() * $configuration['percentage']);
                continue;
            }

            $otherProductsTotal += $item->getTotal();
        }

        if (0 === $promotionAmount) {
            return false;
        }

        $this->fixedDiscountPromotionAdjustmentApplicator->apply($subject, $promotion, -(min($promotionAmount, $otherProductsTotal)));

        return true;
    }

    protected function isConfigurationValid(array $configuration): void
    {
        Assert::keyExists($configuration, 'percentage');
        Assert::greaterThan($configuration['percentage'], 0);
        Assert::lessThanEq($configuration['percentage'], 1);
    }
}
