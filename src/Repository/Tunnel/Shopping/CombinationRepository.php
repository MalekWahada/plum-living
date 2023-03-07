<?php

declare(strict_types=1);

namespace App\Repository\Tunnel\Shopping;

use App\Entity\Tunnel\Shopping\Combination;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CombinationRepository extends EntityRepository
{
    /**
     * @param string $code
     * @return array|Combination[]
     */
    public function findByColorCode(string $code): array
    {
        $qb = $this->createQueryBuilder('com');
        $qb->innerJoin('com.color', 'pov');
        $qb->where('pov.code = :colorCode');
        $qb->setParameter('colorCode', $code);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $optionCode
     * @param string $optionValueCode
     * @return array|Combination[]
     */
    public function findByOptionValueCode(string $optionCode, string $optionValueCode): array
    {
        $qb = $this->createQueryBuilder('com');
        $qb->innerJoin('com.'.$optionCode, 'pov');
        $qb->where('pov.code = :optionValueCode');
        $qb->andWhere('com.recommendation is not null');
        $qb->setParameter('optionValueCode', $optionValueCode);

        return  $qb->getQuery()->getResult();
    }
}
