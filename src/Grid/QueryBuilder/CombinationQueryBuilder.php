<?php

declare(strict_types=1);

namespace App\Grid\QueryBuilder;

use App\Entity\Product\ProductOption;
use App\Repository\Taxon\TaxonRepository;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CombinationQueryBuilder
{
    public function filterByFacade(EntityRepository $entityRepository): QueryBuilder
    {
        $qb = $entityRepository->createQueryBuilder('taxon');
        $qb->andWhere($qb->expr()->in('taxon.code', TaxonRepository::FACADE_TYPE_TAXONS));

        return $qb;
    }

    public function filterByDesignOptionCode(EntityRepository $entityRepository): QueryBuilder
    {
        $qb = $entityRepository->createQueryBuilder('pov');
        $qb->innerJoin('pov.option', 'po');
        $qb->andWhere('po.code = :optionCode');
        $qb->setParameter('optionCode', ProductOption::PRODUCT_OPTION_DESIGN);

        return $qb;
    }

    public function filterByFinishOptionCode(EntityRepository $entityRepository): QueryBuilder
    {
        $qb = $entityRepository->createQueryBuilder('pov');
        $qb->innerJoin('pov.option', 'po');
        $qb->andWhere('po.code = :optionCode');
        $qb->setParameter('optionCode', ProductOption::PRODUCT_OPTION_FINISH);

        return $qb;
    }

    public function filterByColorOptionCode(EntityRepository $entityRepository): QueryBuilder
    {
        $qb = $entityRepository->createQueryBuilder('pov');
        $qb->innerJoin('pov.option', 'po');
        $qb->andWhere('po.code = :optionCode');
        $qb->setParameter('optionCode', ProductOption::PRODUCT_OPTION_COLOR);

        return $qb;
    }
}
