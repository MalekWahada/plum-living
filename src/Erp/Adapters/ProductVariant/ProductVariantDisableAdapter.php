<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Model\Erp\ErpItemModel;
use Psr\Log\LoggerInterface;

class ProductVariantDisableAdapter implements ProductVariantAdapterInterface
{
    private LoggerInterface $erpImportLogger;

    public static function getDefaultPriority(): int
    {
        return 20;
    }

    public function __construct(LoggerInterface $erpImportLogger)
    {
        $this->erpImportLogger = $erpImportLogger;
    }

    /**
     * IsEnabled is COUPLE to the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        if (null === $productVariant->getId()) { // Insert
            $productVariant->disable(); // Product is disabled by default on insert
        } elseif (null !== $productVariant->getId() && !$productVariant->isEnabled()) { // Only for updates
            if (!$erpItem->isInactive()) {
                $this->erpImportLogger->debug(sprintf("[PRODUCT-VARIANT][ACTIVATION] Product variant is manually disabled in Sylius (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
            }// A product variant disabled in shop stays disabled
        } elseif ($erpItem->isInactive()) { // Item disabled on Netsuite
            $productVariant->disable();
            $this->erpImportLogger->debug(sprintf("[PRODUCT-VARIANT][ACTIVATION] Product variant has been disabled from Netsuite (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
        }
    }
}
