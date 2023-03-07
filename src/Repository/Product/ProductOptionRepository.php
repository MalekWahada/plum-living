<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductOption;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductOptionRepository as BaseProductOptionRepository;

class ProductOptionRepository extends BaseProductOptionRepository
{
    /**
     * @param array $codes
     * @return array|ProductOption[]
     */
    public function findByCode(array $codes): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->in('o.code', ':codes'));
        $qb->setParameter('codes', $codes);

        return $qb->getQuery()->getResult();
    }
}
