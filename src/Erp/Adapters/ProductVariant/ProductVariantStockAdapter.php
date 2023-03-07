<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Erp\Providers\NetsuiteStoresProvider;
use App\Model\Erp\ErpItemModel;
use NetSuite\Classes\InventoryItemLocations;
use Psr\Log\LoggerInterface;

class ProductVariantStockAdapter implements ProductVariantAdapterInterface
{
    private NetsuiteStoresProvider $netsuiteStoresProvider;
    private ?array $stores = null;
    private LoggerInterface $erpImportLogger;
    private const FORCE_STOCKS_NOT_TRACKED = true; // Stock are temporary not tracked following a decision by Plum

    public function __construct(
        NetsuiteStoresProvider $netsuiteStoresProvider,
        LoggerInterface $erpImportLogger
    ) {
        $this->netsuiteStoresProvider = $netsuiteStoresProvider;
        $this->erpImportLogger = $erpImportLogger;
    }

    /**
     * Stock is COUPLED with the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        /**
         * Si isSpecialOrderItem == true : Pas de gestion de stock = toujours en vente
         * sinon : locations > quantityAvailable pour les entrepôts disponibles à la vente
         */
        if (self::FORCE_STOCKS_NOT_TRACKED || (isset($erpItem->getItem()->isSpecialOrderItem) && $erpItem->getItem()->isSpecialOrderItem)) { /** @phpstan-ignore-line */
            $productVariant->setOnHand(0);
            $productVariant->setTracked(false);
        } elseif (isset($erpItem->getItem()->locationsList->locations) && is_array($erpItem->getItem()->locationsList->locations)) {
            $this->initStores();
            $quantity = 0;
            foreach ($erpItem->getItem()->locationsList->locations as $location) {
                if ($this->storeActive($location)) {
                    $quantity += $location->quantityAvailable ?? 0;
                }
            }
            if ($quantity === 0) {
                $this->erpImportLogger->info(sprintf("[PRODUCT-VARIANT][STOCKS] Quantity is 0 (internalId=%s, code=%s)", $productVariant->getId(), $productVariant->getCode()));
            }
            $productVariant->setOnHand((int)$quantity);
            $productVariant->setTracked(true);
        }
    }

    /**
     * Load stores from ERP
     */
    private function initStores(): void
    {
        if (null === $this->stores) {
            $this->stores = $this->netsuiteStoresProvider->getStores();
        }
    }

    /**
     * Verify if $location is active
     * @param InventoryItemLocations $location
     * @return bool
     */
    private function storeActive(InventoryItemLocations $location): bool
    {
        return isset($this->stores[$location->location]);
    }
}
