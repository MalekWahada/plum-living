<?php

declare(strict_types=1);

namespace App\Export\Plugin;

use App\Entity\Customer\Customer;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;

class CustomerResourcePlugin extends ResourcePlugin
{
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var Customer $resource */
        foreach ($this->resources as $resource) {
            $this->addDataForResource($resource, 'CompanyVATNumber', $resource->getVatNumber()); // Custom field name
            if (null !== $resource->getPersonalCoupon()) {
                $this->addDataForResource($resource, 'PersonalCoupon', $resource->getPersonalCoupon()->getCode());
            }
            
            $this->addDataForResource($resource, 'CreatedAt', $resource->getCreatedAt() ? $resource->getCreatedAt()->format('d-m-Y H:i:s') : null);
            $this->addDataForResource($resource, 'UpdatedAt', $resource->getUpdatedAt() ? $resource->getUpdatedAt()->format('d-m-Y H:i:s') : null);
        }
    }
}
