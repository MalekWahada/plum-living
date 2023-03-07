<?php

declare(strict_types=1);

namespace App\Erp\Providers;

use App\Erp\NetsuiteService;
use NetSuite\Classes\LocationSearchAdvanced;
use NetSuite\Classes\LocationSearchRow;
use NetSuite\Classes\SearchRequest;
use Psr\Log\LoggerInterface;

final class NetsuiteStoresProvider
{
    private NetsuiteService $service;
    private LoggerInterface $logger;

    private const NETSUITE_STORES_SAVED_SEARCH = "customsearch_prq_tmpl_loc_available_ecom";

    public function __construct(
        LoggerInterface $erpImportLogger,
        NetsuiteService $netsuiteService
    ) {
        $this->logger = $erpImportLogger;
        $this->service = $netsuiteService;
    }

    /**
     * Turn all stores locations.
     * @return array|null
     */
    public function getStores(): ?array
    {
        $search = new LocationSearchAdvanced();
        $scriptId = self::NETSUITE_STORES_SAVED_SEARCH;
        $search->savedSearchScriptId = $scriptId;
        $searchRequest = new SearchRequest();
        $searchRequest->searchRecord = $search;
        $searchResponse = $this->service->search($searchRequest);

        if (!$searchResponse->searchResult->status->isSuccess) {
            $this->logger->error("SEARCH getStores ERROR " . __FILE__ . ' L' . __LINE__);
            return null;
        }

        $stores = [];
        $erpStores = $searchResponse->searchResult->searchRowList->searchRow;
        foreach ($erpStores as $erpStore) {
            /** @var LocationSearchRow $erpStore */
            if (isset($erpStore->basic->internalId[0]->searchValue->internalId)) {
                $storeId = $erpStore->basic->internalId[0]->searchValue->internalId; // @phpstan-ignore-line : do not found basic !
                $stores[$storeId] = [
                    'id' => $storeId,
                    'name' => $erpStore->basic->name[0]->searchValue ?? null,
                ];
            }
        }
        return $stores;
    }
}
