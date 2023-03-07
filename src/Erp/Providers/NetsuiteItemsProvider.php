<?php

declare(strict_types=1);

namespace App\Erp\Providers;

use App\Erp\NetsuiteService;
use App\Exception\Erp\ErpException;
use App\Model\Erp\ErpItemModel;
use NetSuite\Classes\GetListRequest;
use NetSuite\Classes\GetListResponse;
use NetSuite\Classes\ItemSearch;
use NetSuite\Classes\ItemSearchAdvanced;
use NetSuite\Classes\ItemSearchBasic;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\RecordType;
use NetSuite\Classes\SearchDateField;
use NetSuite\Classes\SearchDateFieldOperator;
use NetSuite\Classes\SearchMoreWithIdRequest;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\SearchRow;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

final class NetsuiteItemsProvider
{
    private NetSuiteService $service;
    private int $currentPageIndex = 0;
    private int $totalPages = 1; // At least 1 page to fetch
    private string $requestSearchId;
    private SearchRequest $request;
    private ?FilesystemAdapter $cache;
    private LoggerInterface $logger;

    private const NETSUITE_ITEMS_SAVED_SEARCH = "customsearch_prq_tmpl_searchitems";

    /**
     * For dev usage only ! never should be committed as true !
     */
    private const USE_CACHE = false;
    private bool $isDebug;

    public function __construct(
        LoggerInterface $erpImportLogger,
        KernelInterface $kernel,
        NetsuiteService $netsuiteService
    ) {
        $this->isDebug = $kernel->isDebug();
        $this->logger = $erpImportLogger;
        $this->service = $netsuiteService;

        // Cache
        if ($this->isDebug && self::USE_CACHE) { /** @phpstan-ignore-line */
            $this->cache = new FilesystemAdapter('netsuite-sync', 3600 * 24 * 7, 'var/cache');
        }
    }

    /**
     * Generates a request to search all items in ERP
     */
    public function prepareSearchAllItems(): void
    {
        $search = new ItemSearchAdvanced();
        $search->savedSearchScriptId = self::NETSUITE_ITEMS_SAVED_SEARCH;

        $this->request = new SearchRequest();
        $this->request->searchRecord = $search;
    }

    /**
     * Generates a request to search all items in ERP from a date
     * @param \DateTime $date
     */
    public function prepareSearchItemsSince(\DateTime $date): void
    {
        $search = new ItemSearchAdvanced();
        $search->savedSearchScriptId = self::NETSUITE_ITEMS_SAVED_SEARCH;

        // A partir de cette ligne on construit le critère
        // On construit notre critère pour rechercher sur une date
        $SearchDateField = new SearchDateField();
        $SearchDateField->operator = SearchDateFieldOperator::after; //@phpstan-ignore-line
        $SearchDateField->searchValue = (string) $date->getTimestamp();  // Timestamp

        // On construit une recherche de type "Basic" en combinant un critère avec un champ.
        // Les recherches Basic peuvent être utilisée avec tous les champs standards Netsuite
        // sauf les champs relations.
        // Ici, on construit une recherche pour:
        // lastModifiedDate after "2020-12-13T08:10:34"
        $ItemSearchBasic = new ItemSearchBasic();
        $ItemSearchBasic->lastModifiedDate = $SearchDateField;

        // On créé une recherche sur tous les items ...
        // à laquelle on applique l'expression de recherche
        $ItemSearch = new ItemSearch();
        $ItemSearch->basic = $ItemSearchBasic;

        // On affecte notre expression de recherche à la saved search
        $search->criteria = $ItemSearch;

        $this->request = new SearchRequest();
        $this->request->searchRecord = $search;
    }

    /**
     * Return if the current page is not the last one
     * @return bool
     */
    public function hasNextPageItems(): bool
    {
        return $this->currentPageIndex < $this->totalPages;
    }

    /**
     * Get Record items from the next page
     * @return array|null
     * @throws ErpException
     */
    public function getNextPageItems(): ?array
    {
        $this->currentPageIndex += 1; // Increment current request so each call of this function return the next page even when errors occurs

        /**
         * Using local cache for debug in DEV ONLY
         * Load cache and return records directly
         */
        if ($this->isDebug && self::USE_CACHE && null !== $cacheRecords = $this->getCache($this->currentPageIndex)) { /** @phpstan-ignore-line */
            return array_map(static function ($response) {
                return new ErpItemModel($response);
            }, $cacheRecords->readResponseList->readResponse);
        }

        /**
         * Load page rows if cache disabled or not found
         */
        $rows = null;
        if ($this->currentPageIndex <= $this->totalPages) {
            $rows = $this->getRowsListOfPage($this->currentPageIndex);
        }

        // No data or error in loading rows list
        if (null === $rows) {
            return null;
        }

        // Convert rows to list request.
        $listRequest = $this->getFilteredRowsListRequest($rows);

        return $this->executeListRequestAndProcessResponse($listRequest, true);
    }

    /**
     * Get specific ids from netsuite. Ids can be prefixed with the type with format "x:id" or just "id"
     * @param array $internalIdsTypePrefixed
     * @return array|null
     * @throws ErpException
     */
    public function getItems(array $internalIdsTypePrefixed): ?array
    {
        $listRequest = new GetListRequest();
        $listRequest->baseRef = array();

        foreach ($internalIdsTypePrefixed as $typeAndId) {
            $splitTypeAndId = explode(":", $typeAndId);
            $itemType = 'i'; // Default is inventory item

            // Split input depending on format
            if (count($splitTypeAndId) > 1) { // Format "x:id"
                $itemType = strtolower($splitTypeAndId[0]);
                $id = $splitTypeAndId[1];
            } else { // Format "id"
                $id = $splitTypeAndId[0];
            }

            if (is_numeric($id)) {
                $record = new RecordRef();
                $record->internalId = (string) intval($id); // Take int part of string

                // Item type must be set
                if (str_starts_with($itemType, 'a')) {
                    $record->type = RecordType::assemblyItem;
                } elseif (str_starts_with($itemType, 'g')) {
                    $record->type = RecordType::itemGroup;
                } elseif (str_starts_with($itemType, 'k')) {
                    $record->type = RecordType::kitItem;
                } else {
                    $record->type = RecordType::inventoryItem;
                }

                $listRequest->baseRef[] = $record;
            }
        }

        return $this->executeListRequestAndProcessResponse($listRequest, false); // Get specific item has no caching
    }

    /**
     * @param GetListRequest $listRequest
     * @param bool $hasCache
     * @return array|null
     * @throws ErpException
     */
    private function executeListRequestAndProcessResponse(GetListRequest $listRequest, bool $hasCache = false): ?array
    {
        /**
         * Get records list from ERP
         */
        try {
            $response = $this->service->getList($listRequest);
        } catch (\Exception $e) {
            $this->logger->error("ERP Exception: " . $e->getMessage() . ' ' . __FILE__ . ' L' . __LINE__);
            throw new ErpException("GET ITEMS ERROR: {$e->getMessage()}");
        }

        /**
         * Verify ERP response status
         */
        if (!$response->readResponseList->status->isSuccess) {
            $this->logger->error('ERP GET ITEMS RESPONSE ERROR');
            $this->logger->error(serialize($response->readResponseList->status));
            return null;
        }

        /**
         * Save cache records if enabled
         */
        if ($hasCache && $this->isDebug && self::USE_CACHE) { /** @phpstan-ignore-line */
            $this->saveCache($this->currentPageIndex, $this->totalPages, $this->requestSearchId, $response);
        }

        return array_map(static function ($response) {
            return new ErpItemModel($response);
        }, $response->readResponseList->readResponse);
    }


    /**
     * @param int $pageIndex
     * @param int $totalPages
     * @param string $requestSearchId
     * @param GetListResponse $response
     */
    private function saveCache(int $pageIndex, int $totalPages, string $requestSearchId, GetListResponse $response): void
    {
        try {
            $erpCacheResponse = $this->cache->getItem('erp.loadAllItems.' . $pageIndex);
            $tmpCurrentPage = $this->cache->getItem('erp.currentPageIndex');
            $tmpCurrentPage->set($this->currentPageIndex);
            $tmpTotalPage = $this->cache->getItem('erp.totalPages');
            $tmpTotalPage->set($totalPages);
            $erpCacheResponse->set([
                'currentPageIndex' => $pageIndex,
                'totalPages' => $totalPages,
                'response' => $response,
                'requestSearchId' => $requestSearchId,
            ]);

            $this->cache->save($erpCacheResponse);
        } catch (\Exception | InvalidArgumentException $e) {
            $this->logger->error("Unable to save cache: " . $e->getMessage());
        }
    }

    /**
     * Get data from cache for a specific index
     * @param int $pageIndex
     * @return GetListResponse|null
     */
    private function getCache(int $pageIndex): ?GetListResponse
    {
        try {
            $erpCacheResponse = $this->cache->getItem('erp.loadAllItems.' . $pageIndex);
            if ($erpCacheResponse->isHit()) {
                $this->logger->alert("ERP cache hit, this is not a real update, disable cache in production !");

                $cached = $erpCacheResponse->get();
                $this->currentPageIndex = $cached['currentPageIndex'];
                $this->totalPages = $cached['totalPages'];
                $this->requestSearchId = $cached['requestSearchId'];

                return $cached['response'];
            }
        } catch (\Exception | InvalidArgumentException $e) {
            $this->logger->error("Unable to get cache: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Load ERP page results available for a page on a specific search.
     * @param int $pageIndex
     * @return SearchRow[]
     * @throws ErpException
     */
    private function getRowsListOfPage(int $pageIndex): array
    {
        $this->logger->info("ERP load page " . $pageIndex);

        try {
            // First page request
            if ($pageIndex === 1) {
                $searchResponse = $this->service->search($this->request);
            } else {
                $requestMore = new SearchMoreWithIdRequest();
                $requestMore->searchId = $this->requestSearchId; //TODO: test valid request
                $requestMore->pageIndex = $pageIndex;
                $searchResponse = $this->service->searchMoreWithId($requestMore);
            }
        } catch (\Exception $e) {
            $this->logger->error("ERP Exception: " . $e->getMessage() . ' ' . __FILE__ . ' L' . __LINE__);
            throw new ErpException("SEARCH ERROR page={$pageIndex}: {$e->getMessage()}", $e->getCode(), $e);
        }

        if ($searchResponse->searchResult->status->isSuccess) {
            $this->totalPages = $searchResponse->searchResult->totalPages;
            $this->currentPageIndex = $searchResponse->searchResult->pageIndex;
            $this->requestSearchId = $searchResponse->searchResult->searchId;
        } else {
            throw new ErpException("SEARCH ERROR page={$pageIndex},message=" . $searchResponse->searchResult->status->statusDetail[0]->message);
        }

        return $searchResponse->searchResult->searchRowList->searchRow;
    }

    /**
     * Converts searched rows result to List Item request (ERP requirement) and filter only useful items
     * @param array|null $rows
     * @return GetListRequest
     */
    private function getFilteredRowsListRequest(?array $rows): GetListRequest
    {
        $getListRequest = new GetListRequest();
        $getListRequest->baseRef = array();
        // getList accepte en paramètre une list de RecordRef que l'on construit en parcourant
        // le contenu de la page courante.
        foreach ($rows as $row) {
            $id = $row->basic->internalId[0]->searchValue->internalId;

            $tmpRecordRef = new RecordRef();
            $tmpRecordRef->internalId = $id;
            $itemType = $row->basic->type[0]->searchValue;
            switch ($itemType) {
                case '_discount':
                case '_nonInventoryItem':
                case '_otherCharge':
                case '_service':
                    continue 2;
                case '_inventoryItem':
                    $tmpRecordRef->type = RecordType::inventoryItem;
                    break;
                case '_kit':
                    $tmpRecordRef->type = RecordType::kitItem;
                    break;
                case '_itemGroup':
                    $tmpRecordRef->type = RecordType::itemGroup;
                    break;
                case '_assembly':
                    $tmpRecordRef->type = RecordType::assemblyItem;
                    break;

                default:
                    $this->logger->error("itemType $itemType Not used. in File " . __FILE__ . ' Line ' . __LINE__);
                    continue 2;
            }
            $getListRequest->baseRef[] = $tmpRecordRef;
        }
        return $getListRequest;
    }
}
