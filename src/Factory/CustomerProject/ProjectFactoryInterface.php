<?php

declare(strict_types=1);

namespace App\Factory\CustomerProject;

use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use App\Exception\CustomerProject\DuplicatedProjectItemVariantException;
use App\Exception\CustomerProject\VariantsInconsistencyWithPlumScannerException;
use Sylius\Component\Resource\Factory\TranslatableFactoryInterface;

interface ProjectFactoryInterface extends TranslatableFactoryInterface
{
    /**
     * @return Project
     */
    public function createNew(): Project;

    /**
     * $details array should contains the following two keys:
     * 'scannerProjectId' => Project Identifier retrieved from PlumScanner API
     * 'scannerTransferEmail' => Email retrieved from PlumScanner API that IKEA plan will be transfer to
     *
     * For further details check the swagger docs for PlumScanner API:
     * https://dev-plum-api.azurewebsites.net/api/index.html?urls.primaryName=Plum%20Scanner%20API%20Sylius%20v3
     * Responsible Endpoint : '/api/v3/Scanner/AssignTransferEmail'
     *
     * @param Customer $customer
     * @param Taxon|null $facadeType
     * @param ProductOptionValue|null $designOption
     * @param ProductOptionValue|null $finishOption
     * @param ProductOptionValue|null $colorOption
     * @param array $details
     * @return Project|null
     */
    public function createForCustomerWithOptionsAndScannerDetails(
        Customer $customer,
        ?Taxon $facadeType,
        ?ProductOptionValue $designOption,
        ?ProductOptionValue $finishOption,
        ?ProductOptionValue $colorOption,
        array $details
    ): ?Project;

    /**
     * $details array should contains the following key:
     * 'scannerProjectId' => Project Identifier retrieved from PlumScanner API
     *
     * For further details check the swagger docs for PlumScanner API:
     * https://dev-plum-api.azurewebsites.net/api/index.html?urls.primaryName=Plum%20Scanner%20API%20Sylius%20v3
     * Target Endpoint : '/api/v3/Scanner/SubmitIKPShareLink'
     *
     * @param Customer $customer
     * @param Taxon $facadeType
     * @param ProductOptionValue|null $designOption
     * @param ProductOptionValue|null $finishOption
     * @param ProductOptionValue|null $colorOption
     * @param array $details
     * @return Project|null
     */
    public function createForCustomerWithOptionsAndPlumScannerId(
        Customer $customer,
        Taxon $facadeType,
        ?ProductOptionValue $designOption,
        ?ProductOptionValue $finishOption,
        ?ProductOptionValue $colorOption,
        array $details
    ): ?Project;

    /**
     * @param Project $project
     * @param array $items
     * @return Project
     * @throws DuplicatedProjectItemVariantException
     * @throws VariantsInconsistencyWithPlumScannerException
     */
    public function bindItems(Project $project, array $items): Project;

    public function createForClone(Project $project): Project;

    public function createItemsForClonedProject(Project $project, Project $clone): Project;
}
