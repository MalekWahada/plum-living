<?php

declare(strict_types=1);

namespace App\Provider\Erp;

use App\Entity\Erp\ErpEntity;
use App\Repository\Erp\ErpEntityRepository;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ErpEntityProvider
{
    /**
     * @var ErpEntityRepository
     */
    private ErpEntityRepository $erpEntityRepository;

    /**
     * @var FactoryInterface
     */
    private FactoryInterface $erpEntityFactory;

    /**
     * ErpEntityProvider constructor.
     * @param ErpEntityRepository $erpEntityRepository
     * @param FactoryInterface $erpEntityFactory
     */
    public function __construct(
        ErpEntityRepository $erpEntityRepository,
        FactoryInterface $erpEntityFactory
    ) {
        $this->erpEntityRepository = $erpEntityRepository;
        $this->erpEntityFactory = $erpEntityFactory;
    }

    /**
     * Get or create an ErpEntity
     * @param string $type
     * @param int $erpId
     * @param string $displayName
     * @return ErpEntity
     */
    public function provide(string $type, int $erpId, string $displayName): ErpEntity
    {
        /** @var ?ErpEntity $erpEntity */
        $erpEntity = $this->erpEntityRepository->findOneBy(['type' => $type, 'erpId' => $erpId]);
        if (null === $erpEntity) {
            /** @var ErpEntity $erpEntity */
            $erpEntity = $this->erpEntityFactory->createNew();
            $erpEntity->setType($type);
            $erpEntity->setErpId($erpId);
            $erpEntity->setName($displayName ?? '');
            //persist ErpEntity
            $this->erpEntityRepository->add($erpEntity);
        }
        return $erpEntity;
    }
}
