<?php

declare(strict_types=1);

namespace App\Factory\CustomerProject;

use App\Entity\CustomerProject\Project;
use App\Entity\CustomerProject\ProjectItem;
use App\Entity\Product\ProductGroup;
use App\Entity\Product\ProductVariant;
use App\Exception\CustomerProject\DuplicatedProjectItemVariantException;
use App\Exception\CustomerProject\VariantsInconsistencyWithPlumScannerException;
use App\Model\CustomerProject\ProjectItemFormModel;
use Sylius\Component\Resource\Factory\TranslatableFactoryInterface;

interface ProjectItemFactoryInterface extends TranslatableFactoryInterface
{
    public function createNew(): ProjectItem;

    /**
     * $details array comes from PlumScanner API and should have the following format :
     * [
     *      'PlumLabel' => string,
     *      'IkeaSKU' => string,
     *      'IkeaUnitPrice' => number($double),
     *      'IkeaQuantity' => integer($int32),
     *      'CabinetReference' => string,
     *      'IkeaTotalPrice' => number($double),
     *      'CabinetReference' => string,
     *      'SyliusProductOptions' => [
     *            'SKU' => string,
     *            'SyliusId' => integer($int32),
     *            'Quantity' => integer($int32),
     *      ],
     * ]
     *
     * For further details check the swagger docs for PlumScanner API:
     * https://dev-plum-api.azurewebsites.net/api/index.html?urls.primaryName=Plum%20Scanner%20API%20Sylius%20v3
     * Responsible Endpoint : '/api/v3/Scanner/Project'
     *
     * @param Project $project
     * @param array $details
     * @return ProjectItem
     * @throws DuplicatedProjectItemVariantException
     * @throws VariantsInconsistencyWithPlumScannerException
     */
    public function createForProjectWithScannerDetails(Project $project, array $details): ProjectItem;

    /**
     * $variants is an array of variants entries coming from
     * <PlumScannerApiClientInterface::getVariantsFromLabel> API call
     *
     * @param array $variants
     * @return ProjectItem
     */
    public function createForScannerVariants(array $variants): ProjectItem;

    /**
     * @param array|ProductVariant[] $variants
     * @return ProjectItem
     */
    public function createForProductVariants(array $variants): ProjectItem;

    /**
     * Clones the project item
     * @param ProjectItem $item
     * @return ProjectItem
     */
    public function createCloneFromItem(ProjectItem $item): ProjectItem;

    /**
     * Convert from form model to entity model
     * @param ProjectItemFormModel $formModel
     * @return ProjectItem|null
     */
    public function createForFormModel(ProjectItemFormModel $formModel): ?ProjectItem;

    /**
     * Convert from entity model to form model
     * @param ProjectItem $item
     * @return ProjectItemFormModel
     */
    public function createFormModelFromItem(ProjectItem $item): ProjectItemFormModel;

    /**
     * Create an item from a product group.
     * ProjectItemVariants are dynamically created from product group variants
     * @param ProductGroup $productGroup
     * @return ProjectItem
     */
    public function createForProductGroup(ProductGroup $productGroup): ProjectItem;
}
