<?php

declare(strict_types=1);

namespace App\Broker;

use App\Calculator\ProductPriceTaxCalculator;
use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Entity\CustomerProject\ProjectItem;
use App\Entity\Product\ProductOptionValue;
use App\Erp\Adapters\ProductVariant\ProductVariantPriceAdapter;
use App\Exception\CustomerProject\UnfetchedProjectException;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use function Sentry\captureException;

final class PlumScannerApiClient implements PlumScannerApiClientInterface
{
    // plum scanner api Project endpoints list
    private const ENDPOINT_RETRIEVE_USER_ID = '/Scanner/UserId';
    private const ENDPOINT_ASSIGN_TRANSFER_EMAIL = '/Scanner/AssignTransferEmail';
    private const ENDPOINT_PROJECT_STATUS = '/Scanner/ProjectStatus';
    private const ENDPOINT_PROJECT_DETAILS = '/Scanner/Project';
    private const ENDPOINT_PRODUCT_LABELS = '/Scanner/ListProductLabels';
    private const ENDPOINT_PRODUCT_LINE_FROM_LABEL = '/Scanner/AddLineFromLabel';
    private const ENDPOINT_GENERATE_QUOTE_FILE_LINK = '/Scanner/GenerateQuoteFileLink';
    private const ENDPOINT_GENERATE_PDF_QUOTE_FILE_LINK = '/Scanner/GeneratePDFQuoteFileLink';
    private const ENDPOINT_SHARE_PROJECT_VIA_LINK = '/Scanner/SubmitIKPShareLink';

    // plum scanner api Analytics endpoints list
    public const ENDPOINT_MAIL_TUNNEL_STARTED = '/Analytics/MailTunnelStarted';
    public const ENDPOINT_PDF_TUNNEL_STARTED = '/Analytics/PDFTunnelStarted';
    public const ENDPOINT_USER_MAIL_SENT = '/Analytics/UserMailSent';
    public const ENDPOINT_PROJECT_LOADED = '/Analytics/ProjectLoaded';
    public const ENDPOINT_SCHEDULE_CALL = '/Analytics/ScheduleCall';
    public const ENDPOINT_ADD_TO_CART = '/Analytics/AddToCart';
    public const ENDPOINT_ERROR_POPUP = '/Analytics/ErrorPopup';
    public const ENDPOINT_WEBSITE_EXCEPTION = '/Analytics/WebsiteException';

    // plum scanner api Sylius DB Management endpoints list
    public const ENDPOINT_UPDATE_INTERNAL_SYLIUS_DATABASE = '/SyliusDbManagement/UpdateInternalSyliusDatabase';
    public const ENDPOINT_ASSESS_DB_CONSISTENCY = '/SyliusDbManagement/AssessDbConsistency';

    // project status list
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_ERROR_INVALID_EMAIL = 'error_invalid_mail';
    public const STATUS_ERROR_INVALID_PDF = 'error_invalid_pdf';
    public const STATUS_ERROR_OTHER = 'error_other';
    public const STATUS_WAITING_FOR_EMAIL = 'waiting_for_mail';
    public const STATUS_SCAN_PROCESSING = 'processing';
    public const STATUS_SCAN_COMPLETED = 'ok';
    public const STATUS_SCAN_MISSING_FRONT = 'no_front_match';

    public const SUCCESS_SCAN = [
        self::STATUS_WAITING_FOR_EMAIL,
        self::STATUS_SCAN_PROCESSING,
        self::STATUS_SCAN_COMPLETED,
    ];

    public const ANALYTICS_ENDPOINTS = [
        self::ENDPOINT_MAIL_TUNNEL_STARTED,
        self::ENDPOINT_PDF_TUNNEL_STARTED,
        self::ENDPOINT_USER_MAIL_SENT,
        self::ENDPOINT_PROJECT_LOADED,
        self::ENDPOINT_SCHEDULE_CALL,
        self::ENDPOINT_ADD_TO_CART,
        self::ENDPOINT_ERROR_POPUP,
        self::ENDPOINT_WEBSITE_EXCEPTION,
    ];

    public const SYLIUS_DB_MANAGEMENT_ENDPOINTS = [
        self::ENDPOINT_UPDATE_INTERNAL_SYLIUS_DATABASE,
        self::ENDPOINT_ASSESS_DB_CONSISTENCY,
    ];

    private const AUTHENTICATION_ERROR_MESSAGE = 'Invalid UserId';

    private string $plumScannerApiBaseUrl;

    private HttpClientInterface $httpClient;
    private ProductPriceTaxCalculator $productPriceTaxCalculator;
    private RepositoryInterface $customerRepository;
    private LoggerInterface $logger;
    private RouterInterface $router;
    private LocaleContextInterface $localeContext;

    public function __construct(
        string $plumScannerApiBaseUrl,
        HttpClientInterface $plumScannerApiClient,
        ProductPriceTaxCalculator $productPriceTaxCalculator,
        RepositoryInterface $customerRepository,
        LoggerInterface  $logger,
        RouterInterface $router,
        LocaleContextInterface $localeContext
    ) {
        $this->plumScannerApiBaseUrl = $plumScannerApiBaseUrl;
        $this->httpClient = $plumScannerApiClient;
        $this->productPriceTaxCalculator = $productPriceTaxCalculator;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->router = $router;
        $this->localeContext = $localeContext;
    }

    private function getUserId(Customer $customer): ? string
    {
        try {
            // retrieve userId from PlumScanner API
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_RETRIEVE_USER_ID,
                [
                    'query' => [
                        'userEmail' => $customer->getEmail()
                    ]
                ]
            );

            if (!isset($response->toArray()['user_id'])) {
                return null;
            }

            $customer->setPlumScannerUserId($response->toArray()['user_id']);
            $this->customerRepository->add($customer);

            return $customer->getPlumScannerUserId();
        } catch (Throwable $exception) {
            captureException($exception);

            return null;
        }
    }

    public function createProject(Customer $customer): ?array
    {
        try {
            $userId = $this->getUserId($customer);

            if (null === $userId) {
                return null;
            }

            // push analytics data to plum scanner
            $this->pushAnalyticsData($customer, self::ENDPOINT_MAIL_TUNNEL_STARTED);

            // create new project for given user
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_ASSIGN_TRANSFER_EMAIL,
                [
                    'query' => [
                        'userId' => $userId,
                        'localeCode' => $this->localeContext->getLocaleCode(),
                    ]
                ]
            );

            $data = $response->toArray();
            unset($data['http_code']);

            return $data;
        } catch (Throwable $exception) {
            return null;
        }
    }


    public function createProjectViaLink(Customer $customer, string $link): ?array
    {
        try {
            $userId = $customer->getPlumScannerUserId() ?? $this->getUserId($customer);

            if (null === $userId) {
                return null;
            }

            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_SHARE_PROJECT_VIA_LINK,
                [
                    'query' => [
                        'userId' => $userId,
                        'projectShareLink' => $link,
                        'localeCode' => $this->localeContext->getLocaleCode()
                    ]
                ]
            );

            return $response->toArray();
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function getProjectStatus(Project $project): ?string
    {
        try {
            // check project status
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_PROJECT_STATUS,
                [
                    'query' => [
                        'projectId' => $project->getScannerProjectId()
                    ]
                ]
            );

            return $response->toArray()['project_status'];
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function getProjectCompleteStatus(Project $project): ?array
    {
        try {
            // check project status
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_PROJECT_STATUS,
                [
                    'query' => [
                        'projectId' => $project->getScannerProjectId()
                    ]
                ]
            );

            return $response->toArray();
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function getProjectDetails(Project $project): ?array
    {
        $customer = $project->getCustomer();

        try {
            // get project details
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_PROJECT_DETAILS,
                [
                    'query' => [
                        'projectId' => $project->getScannerProjectId(),
                        'userId' => $customer->getPlumScannerUserId()
                    ]
                ]
            );

            return $response->toArray();
        } catch (Throwable $exception) {
            if ($exception instanceof ClientException && $this->isAuthenticationFailed($exception)) {
                $this->getUserId($customer);
                return $this->getProjectDetails($project);
            }

            return null;
        }
    }

    public function bindProjectProgress(string $status): int
    {
        switch ($status) {
            case self::STATUS_SCAN_PROCESSING:
                return 50;
            case self::STATUS_SCAN_COMPLETED:
                return 100;
            case self::STATUS_WAITING_FOR_EMAIL:
            default:
                return 0;
        }
    }

    public function getProductsLabels(): ?array
    {
        try {
            // check project status
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_PRODUCT_LABELS,
            );

            return $response->toArray();
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function getVariantsFromLabel(?string $signId, Customer $customer): ?array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_PRODUCT_LINE_FROM_LABEL,
                [
                    'query' => [
                        'labelId' => $signId,
                        'userId' => $customer->getPlumScannerUserId()
                    ]
                ]
            );

            return $response->toArray()['SyliusProductOptions'];
        } catch (Throwable $exception) {
            if ($exception instanceof ClientException && $this->isAuthenticationFailed($exception)) {
                $this->getUserId($customer);
                return $this->getVariantsFromLabel($signId, $customer);
            }

            return null;
        }
    }

    public function pushAnalyticsData(Customer $customer, string $endpoint): void
    {
        if (!in_array($endpoint, self::ANALYTICS_ENDPOINTS)) {
            return;
        }

        try {
            $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . $endpoint,
                [
                    'query' => [
                        'userId' => $customer->getPlumScannerUserId()
                    ]
                ]
            );
        } catch (Throwable $exception) {
            if ($exception instanceof ClientException && $this->isAuthenticationFailed($exception)) {
                $this->getUserId($customer);
                $this->pushAnalyticsData($customer, $endpoint);
            }

            $this->logger->error($exception->getMessage());
        }
    }

    public function synchronizeDatabaseWithScanner(string $endpoint): void
    {
        if (!in_array($endpoint, self::SYLIUS_DB_MANAGEMENT_ENDPOINTS)) {
            return;
        }

        try {
            $this->httpClient->request(
                'GET',
                $this->plumScannerApiBaseUrl . $endpoint
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function generatePDFQuoteFileLink(Project $project, ChannelInterface $channel): ?string
    {
        if (null !== $project->getScannerProjectId() && !$project->isScannerFetched()) {
            throw new UnfetchedProjectException('Project items are not loaded');
        }

        $queryPayload = [
            'query' => ['userId' => $project->getCustomer()->getPlumScannerUserId()],
            'json' => [
                'projectShareLink' => $this->router->generate(
                    'app_customer_project_share',
                    ['token' => $project->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'quoteNumber' => $project->getId(),
                'projectName' => $project->getName(),
                'quoteItems' => $this->getQuoteItemsAsArray($project, $channel),
                'localeCode' => $this->localeContext->getLocaleCode(),
                'comment' => $project->getComment(),
            ],
        ];

        $loggingContext = [];
        try {
            $response = $this->httpClient->request(
                'POST',
                $this->plumScannerApiBaseUrl . self::ENDPOINT_GENERATE_PDF_QUOTE_FILE_LINK,
                $queryPayload
            );

            try {
                $responseData = $response->toArray();
            } catch (ClientException $exception) {
                $loggingContext[] = $response->getContent(false);
                throw $exception;
            }

            if (!isset($responseData['downloadPath'])) {
                $this->logger->error(sprintf('PDF link is missing for project "%s"', $project->getId()), [
                    $queryPayload
                ]);

                return null;
            }

            return $responseData['downloadPath'];
        } catch (Throwable $exception) {
            if ($exception instanceof ClientException && $this->isAuthenticationFailed($exception)) {
                $this->getUserId($project->getCustomer());
                return $this->generatePDFQuoteFileLink($project, $channel);
            }

            $this->logger->error($exception->getMessage(), $loggingContext);
            return null;
        }
    }

    /**
     * @param Project $project
     * @param ChannelInterface $channel
     * @return string
     * @throws \JsonException
     */
    private function getQuoteItemsAsJson(Project $project, ChannelInterface $channel): string
    {
        return json_encode(['quoteItems' => $this->getQuoteItemsAsArray($project, $channel)], JSON_THROW_ON_ERROR);
    }

    private function isAuthenticationFailed(ClientException $exception) : bool
    {
        $response = $exception->getResponse();
        $responseContent = null;
        $statusCode = null;

        try {
            $statusCode = $response->getStatusCode();
            $responseContent = $response->getContent(false);

            $responseData = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);

            $errorMessage = $responseData['error_message'] ?? $responseContent;
        } catch (\JsonException $jsonException) {
            $errorMessage = $responseContent;
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }

        return (Response::HTTP_BAD_REQUEST === $statusCode) && (self::AUTHENTICATION_ERROR_MESSAGE === $errorMessage);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getQuoteItemsAsArray(Project $project, ChannelInterface $channel): array
    {
        $quoteItems = [];

        foreach ($project->getItems() as $item) {
            $chosenVariant = $item->getChosenVariant();

            if (null === $chosenVariant ||
                null === $chosenVariant->getProductVariant() ||
                null === $chosenVariant->getProductVariant()->getProduct() ||
                !$chosenVariant->getProductVariant()->hasChannelPricingForChannel($channel)) {
                continue;
            }

            // Extract option values from chosen variant
            $optionValues = array_values(array_filter(array_map(static function (string $optionCode) use ($chosenVariant) {
                return $chosenVariant->getOptionValue($optionCode);
            }, ProjectItem::AVAILABLE_OPTION_CODES), static function ($optionValue) {
                return null !== $optionValue;
            }));

            $quoteItems[] = [
                'cabinet' => $item->getCabinetReference(),
                'description' => !empty($item->getPlumLabel()) ? $item->getPlumLabel() : $chosenVariant->getProductVariant()->getProduct()->getName(),
                'plumSKU' => $chosenVariant->getProductVariant()->getCode(),
                'plumQuantity' => $chosenVariant->getQuantity(),
                'plumUnitPrice' => $this->productPriceTaxCalculator->calculate($chosenVariant->getProductVariant())
                    / ProductVariantPriceAdapter::PRICE_PRECISION,
                'options' => array_map(static function (ProductOptionValue $optionValue) {
                    return $optionValue->getValue();
                }, $optionValues),
                'comment' => $item->getComment(),
            ];
        }

        return $quoteItems;
    }
}
