<?php

declare(strict_types=1);

namespace App\Erp;

use App\Erp\Providers\NetsuiteItemsProvider;
use App\Exception\Erp\ErpException;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;

final class NetsuiteSync
{
    const TIME_LIMIT = 3600*6; // 6h limit.

    private NetsuiteItemsProvider $itemsProvider;
    private ProductFactoryInterface $productFactory;
    private NetsuiteItemTransformer $itemTransformer;
    private NetsuiteFileLogger $netsuiteFileLogger;
    private LoggerInterface $erpImportLogger;
    private int $startTime;

    public function __construct(
        NetsuiteItemsProvider $netsuiteItemsProvider,
        ProductFactoryInterface $productFactory,
        NetsuiteItemTransformer $netsuiteItemTransformer,
        NetsuiteFileLogger $netsuiteFileLogger,
        LoggerInterface $erpImportLogger
    ) {
        $this->itemsProvider = $netsuiteItemsProvider;
        $this->productFactory = $productFactory;
        $this->itemTransformer = $netsuiteItemTransformer;
        $this->netsuiteFileLogger = $netsuiteFileLogger;
        $this->erpImportLogger = $erpImportLogger;

        $this->startTime = time();
    }

    /**
     * Sync all product
     * @param bool $logToJson
     * @param bool $logToCsv
     */
    public function syncAll(bool $logToJson = false, bool $logToCsv = false): void
    {
        $this->erpImportLogger->info("Start ERP Sync");
        $this->itemsProvider->prepareSearchAllItems();

        while ($this->itemsProvider->hasNextPageItems()) {
            try {
                $items = $this->itemsProvider->getNextPageItems();
                $this->processItems($items, $logToJson, $logToCsv);

                /**
                 * Prevent script too long execution
                 */
                if (time() - $this->startTime > self::TIME_LIMIT) {
                    $this->erpImportLogger->error("Script timeout after: " . (time() - $this->startTime) . " seconds");
                    throw new \RuntimeException("Script Timeout");
                }
            } catch (ErpException $e) {
                $this->erpImportLogger->error("Error while getting Netsuite page: " . $e->getMessage());
            }
        }

        $this->erpImportLogger->info("ERP Sync Finished");
    }

    /**
     * Sync only specific ids
     * Meant for debugging
     * Ids can be prefixed with the type of the item
     * Format: "a:1234" for assembly / "i:1234" for inventory / "k:1234" for kit / "g:1234" for group
     * @param array $internalIdsTypePrefixed
     * @param bool $logToJson
     * @param bool $logToCsv
     */
    public function syncSpecificIds(array $internalIdsTypePrefixed, bool $logToJson = false, bool $logToCsv = false): void
    {
        $this->erpImportLogger->info("Syncing ERP specific ids: " . implode(", ", $internalIdsTypePrefixed));
        $this->itemsProvider->prepareSearchAllItems();

        try {
            $items = $this->itemsProvider->getItems($internalIdsTypePrefixed);
            $this->processItems($items, $logToJson, $logToCsv);
        } catch (ErpException $e) {
            $this->erpImportLogger->error("Error while getting Netsuite specific ids: " . $e->getMessage());
        }

        $this->erpImportLogger->info("ERP Sync Finished");
    }

    /**
     * @param array|null $items
     * @param bool $logToJson
     * @param bool $logToCsv
     */
    private function processItems(?array $items, bool $logToJson, bool $logToCsv): void
    {
        if (null === $items) {
            return;
        }

        foreach ($items as $item) {
            try {
                // Log item
                if ($logToJson) {
                    $this->netsuiteFileLogger->logErpModel($item);
                }
                if ($logToCsv) {
                    $this->netsuiteFileLogger->logErpModelToCsv($item);
                }

                // Transform item
                $this->itemTransformer->transform($item);
            } catch (ErpException $e) {
                $this->erpImportLogger->error("Error while transforming Netsuite item (" . $item->getId() . "): " . $e->getMessage());
            }
        }
    }
}
