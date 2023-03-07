<?php

declare(strict_types=1);

namespace App\Erp;

use App\Datadog\Application\DatadogClientInterface;
use Exception;
use NetSuite\NetSuiteService as BaseNetSuiteService;
use Psr\Log\LoggerInterface;

final class NetsuiteService extends BaseNetSuiteService
{
    public const RESULTS_PER_PAGE = 150;
    public const REQUEST_RETRIES = 4;
    public const REQUEST_RETRY_DELAY = 10; // in seconds

    private LoggerInterface $logger;
    private DatadogClientInterface $datadogClient;
    private int $requestRetries = 0;

    public function __construct(LoggerInterface $erpImportLogger, DatadogClientInterface $datadogClient)
    {
        $this->logger = $erpImportLogger;
        $this->datadogClient = $datadogClient;

        // Prepare config. Credentials are automatically extracted from ENV
        parent::__construct();

        // Set xx results per page.
        $this->setSearchPreferences(true, self::RESULTS_PER_PAGE, true);
    }

    /**
     * Wrap original method and retry request if error append
     * @param mixed $operation
     * @param mixed $parameter
     * @return mixed
     * @throws Exception
     */
    protected function makeSoapCall($operation, $parameter)
    {
        try {
            $res = parent::makeSoapCall($operation, $parameter);
            $this->requestRetries = 0; // Reset counter
            return $res;
        } catch (Exception $e) {
            $tags = [
                'operation' => $operation,
            ];

            if ($this->requestRetries < self::REQUEST_RETRIES) {
                ++$this->requestRetries;
                $delay = $this->requestRetries * self::REQUEST_RETRY_DELAY;

                $this->logger->warning(sprintf("An error occurred when executing request to ERP: %s. Retrying in %d seconds (attempt %d/%d)", $e->getMessage(), $delay, $this->requestRetries, self::REQUEST_RETRIES));
                $this->datadogClient->increment('erp.request_retries', $tags);

                sleep($delay);

                return self::makeSoapCall($operation, $parameter);
            }

            $this->requestRetries = 0; // Reset counter
            $this->datadogClient->increment('erp.request_failed', $tags);
            throw $e;
        }
    }
}
