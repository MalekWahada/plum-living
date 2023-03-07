<?php

declare(strict_types=1);

namespace App\Broker;

use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Exception\CustomerProject\UnfetchedProjectException;
use Sylius\Component\Core\Model\ChannelInterface;

interface PlumScannerApiClientInterface
{
    /**
     * Create and retrieve plum scanner project details for given customer in following format :
     *
     * [
     *      'transfer_email' => string,
     *      'project_id' => string,
     *      'user_id' => string
     * ]
     *
     * @param Customer $customer
     * @return array|null
     */
    public function createProject(Customer $customer): ?array;

    /**
     * Create and retrieve plum scanner project details given a customer and an IKEA project link.
     * we expect the success response in following format :
     *
     * [
     *      'project_id': string,
     * ]
     *
     * @param Customer $customer
     * @param string $link
     * @return array|null
     */
    public function createProjectViaLink(Customer $customer, string $link): ?array;

    /**
     * Retrieve plum scanner project status with following possible results :
     * - not_found
     * - waiting_for_mail
     * - processing
     * - ok
     * - error_invalid_mail
     * - error_invalid_pdf
     * - error_other
     *
     * @param Project $project
     * @return string|null
     */
    public function getProjectStatus(Project $project): ?string;

    /**
     * Retrieve plum scanner project complete status with following possible results for status :
     * - not_found
     * - waiting_for_mail
     * - processing
     * - ok
     * - error_invalid_mail
     * - error_invalid_pdf
     * - error_other
     *
     * @param Project $project
     * @return array|null
     */
    public function getProjectCompleteStatus(Project $project): ?array;

    /**
     * Retrieve project details in following format :
     *
     *  [
     *      'data' =>  [
     *          0 => [
     *              'PlumLabel' => string,
     *              'IkeaSKU' => string,
     *              'IkeaUnitPrice' => number($double),
     *              'IkeaQuantity' => integer($int32),
     *              'CabinetReference' => string,
     *              'IkeaTotalPrice' => number($double),
     *              'CabinetReference' => string,
     *              'SyliusProductOptions' => [
     *                  'SKU' => string,
     *                  'SyliusId' => integer($int32),
     *                  'Quantity' => integer($int32),
     *              ],
     *          ],
     *          ...
     *      ],
     *  ]
     *
     * For further details check the swagger docs for PlumScanner API:
     * https://dev-plum-api.azurewebsites.net/api/index.html?urls.primaryName=Plum%20Scanner%20API%20Sylius%20v3
     * Responsible Endpoint : '/api/v3/Scanner/Project'
     *
     * @param Project $project
     * @return array|null
     */
    public function getProjectDetails(Project $project): ?array;

    /**
     * Returns (int) one of the following possibilities as percentage:
     * if $status === 'waiting_for_mail' -> returns 0
     * if $status === 'processing' -> returns 50
     * if $status === 'ok' -> returns 100
     *
     * @param string $status
     * @return int|null
     */
    public function bindProjectProgress(string $status): ?int;

    /**
     * Retrieve products labels
     *
     * @return array|null
     */
    public function getProductsLabels(): ?array;

    /**
     * Retrieve products variants for given product label
     *
     * @param string|null $signId Product Label Unique Identifier
     * @param Customer $customer
     * @return array|null
     */
    public function getVariantsFromLabel(?string $signId, Customer $customer): ?array;

    public function pushAnalyticsData(Customer $customer, string $endpoint): void;

    public function synchronizeDatabaseWithScanner(string $endpoint): void;

    /**
     * @param Project $project
     * @param ChannelInterface $channel
     * @return string|null
     * @throws UnfetchedProjectException
     */
    public function generatePDFQuoteFileLink(Project $project, ChannelInterface $channel): ?string;
}
