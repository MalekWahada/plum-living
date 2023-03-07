<?php

namespace App\Repository\Product;

use App\Entity\Product\DeliveryDateCalculationConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class DeliveryDateCalculationConfigRepository extends EntityRepository
{
    public function findByMode(string $mode): ?DeliveryDateCalculationConfig
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.mode = :mode')
                ->setParameter('mode', $mode)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
