<?php

declare(strict_types=1);

namespace App\Repository\Promotion;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Repository\PromotionRepositoryInterface as BasePromotionRepositoryInterface;

interface PromotionRepositoryInterface extends BasePromotionRepositoryInterface
{
    public function findNotGeneratedPromotionsQueryBuilder(): QueryBuilder;
}
