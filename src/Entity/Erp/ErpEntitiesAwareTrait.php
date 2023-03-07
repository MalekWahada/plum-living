<?php

declare(strict_types=1);

namespace App\Entity\Erp;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait ErpEntitiesAwareTrait
{
    /**
     * @var Collection|ErpEntity[]|ArrayCollection
     */
    protected Collection $erpEntities;

    public function __construct()
    {
        $this->erpEntities = new ArrayCollection();
    }

    /**
     * @return Collection|ErpEntity[]
     */
    public function getErpEntities(): Collection
    {
        return $this->erpEntities;
    }

    public function hasErpEntities(): bool
    {
        return !$this->getErpEntities()->isEmpty();
    }

    public function hasErpEntity(ErpEntity $erpEntity): bool
    {
        return $this->getErpEntities()->contains($erpEntity);
    }

    public function addErpEntity(ErpEntity $erpEntity): void
    {
        if (!$this->hasErpEntity($erpEntity)) {
            $this->erpEntities->add($erpEntity);
        }
    }

    public function removeErpEntity(ErpEntity $erpEntity): void
    {
        if ($this->hasErpEntity($erpEntity)) {
            $this->erpEntities->removeElement($erpEntity);
        }
    }
}
