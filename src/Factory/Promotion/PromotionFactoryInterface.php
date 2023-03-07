<?php

declare(strict_types=1);

namespace App\Factory\Promotion;

use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface PromotionFactoryInterface extends FactoryInterface
{
    public function createForB2bProgram(): PromotionInterface;
}
