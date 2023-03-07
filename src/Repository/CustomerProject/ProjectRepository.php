<?php

declare(strict_types=1);

namespace App\Repository\CustomerProject;

use App\Broker\PlumScannerApiClient;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\CustomerInterface;

class ProjectRepository extends EntityRepository
{
    public function getLastId(): int
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('MAX(o.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function getCustomerProjects(CustomerInterface $customer): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.customer', ':customer'),
                $qb->expr()->orX(
                    $qb->expr()->andX( // Case coming from Scanner
                        $qb->expr()->isNotNull('p.scannerProjectId'),
                        $qb->expr()->eq('p.scannerStatus', ':scannerStatus'),
                        $qb->expr()->eq('p.scannerFetched', true)
                    ),
                    $qb->expr()->isNull('p.scannerProjectId') // Case not from Scanner
                )
            ))
            ->orderBy('p.updatedAt', 'DESC')
            ->setParameter('customer', $customer)
            ->setParameter('scannerStatus', PlumScannerApiClient::STATUS_SCAN_COMPLETED)
        ;

        return $qb->getQuery()->getResult();
    }
}
