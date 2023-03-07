<?php

declare(strict_types=1);

namespace App\Repository\Promotion;

use App\Promotion\Checker\Rule\FrontSamplePromotionRuleChecker;
use App\Promotion\Checker\Rule\PaintSamplePromotionRuleChecker;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PromotionRepository as BasePromotionRepository;

class PromotionRepository extends BasePromotionRepository implements PromotionRepositoryInterface
{
    /**
     * Get all promotions which does not contain any sample promotion rule
     * Also get promotions with no rules
     */
    public function findNotGeneratedPromotionsQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        $qb->leftJoin('p.rules', 'r')
            ->orWhere($qb->expr()->andX(
                $qb->expr()->neq('r.type', ':frontPromotionType'),
                $qb->expr()->neq('r.type', ':paintPromotionType')
            ))
            ->orWhere($qb->expr()->isNull('r')) // Also keep promotions with no rules
            ->setParameter('frontPromotionType', FrontSamplePromotionRuleChecker::TYPE)
            ->setParameter('paintPromotionType', PaintSamplePromotionRuleChecker::TYPE)
        ;
        return $qb;
    }
}
