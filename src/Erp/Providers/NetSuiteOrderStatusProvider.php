<?php

declare(strict_types=1);

namespace App\Erp\Providers;

use App\Erp\Slugifier;

class NetSuiteOrderStatusProvider
{
    // possible Net-suite erp-statuses are: 'Annulée','En attente de paiment','En préparation / production','En cours d'expédition','Livrée', 'Expédiée'.
    // The 'En attente de paiment' isn't handled via Net-Suite
    public const ORDER_ERP_STATUS_CANCELED = 'annulee';
    public const ORDER_ERP_STATUS_PRODUCTION_IN_PROGRESS = 'en_preparation_production';
    public const ORDER_ERP_STATUS_DELIVERY_IN_PROGRESS = 'en_cours_d_expedition';
    public const ORDER_ERP_STATUS_BILLED = 'livree';
    public const ORDER_ERP_STATUS_DELIVERED = 'expediee';

    public function getSluggedStatus(string $erpStatus): string
    {
        return Slugifier::slugifyOrderERPStatus($erpStatus);
    }
}
