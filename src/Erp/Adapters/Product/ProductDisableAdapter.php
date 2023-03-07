<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Model\Erp\ErpItemModel;
use Psr\Log\LoggerInterface;

class ProductDisableAdapter implements ProductAdapterInterface
{
    private LoggerInterface $erpImportLogger;

    public function __construct(LoggerInterface $erpImportLogger)
    {
        $this->erpImportLogger = $erpImportLogger;
    }

    /**
     * IsEnabled is COUPLED to the ERP
     * @param Product $product
     * @param ErpItemModel $erpItem
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        if (null === $product->getId()) { // Insert
            $product->disable(); // Product is disabled by default on insert
        } elseif (null !== $product->getId() && !$product->isEnabled()) { // Only for updates
            if (!$erpItem->isInactive()) {
                $this->erpImportLogger->debug(sprintf("[PRODUCT][ACTIVATION] Product is manually disabled in Sylius (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
            }// A product variant disabled in shop stays disabled
        } elseif ($erpItem->isInactive()) { // Item disabled on Netsuite
            $product->disable();
            $this->erpImportLogger->debug(sprintf("[PRODUCT][ACTIVATION] Product has been disabled from Netsuite (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
        }
    }
}
