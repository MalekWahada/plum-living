<?php

declare(strict_types=1);

namespace App\Repository\Erp;

use App\Entity\Erp\ErpEntity;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @method ErpEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ErpEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ErpEntity[]    findAll()
 * @method ErpEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ErpEntityRepository extends EntityRepository
{
}
