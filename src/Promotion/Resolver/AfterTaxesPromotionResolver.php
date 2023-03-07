<?php

declare(strict_types=1);

namespace App\Promotion\Resolver;

use App\Promotion\Action\OrderPercentageOfFrontItemsDiscountCommand;
use App\Promotion\Action\FixedDiscountPromotionActionCommand;
use App\Promotion\Action\OrderPercentageDiscountCommand;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;

class AfterTaxesPromotionResolver
{
    public function isAfterTax(PromotionActionCommandInterface $command): bool
    {
        return $command instanceof FixedDiscountPromotionActionCommand
            || $command instanceof OrderPercentageDiscountCommand
            || $command instanceof OrderPercentageOfFrontItemsDiscountCommand;
    }
}
