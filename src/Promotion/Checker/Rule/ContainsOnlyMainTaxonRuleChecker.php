<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use App\Entity\Order\Order;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class ContainsOnlyMainTaxonRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'contains_only_main_taxon_promotion';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!isset($configuration['taxons']) || !is_array($configuration['taxons'])) {
            return false;
        }

        if (!$subject instanceof Order) {
            throw new UnsupportedTypeException($subject, Order::class);
        }

        // Search for products matching the configured taxon
        foreach ($subject->getItems() as $item) {
            $product = $item->getProduct();
            if (null === $product || null === $product->getMainTaxon()) {
                continue;
            }

            if (!in_array($product->getMainTaxon()->getCode(), $configuration['taxons'], true)) {
                return false;
            }
        }
        return true;
    }
}
