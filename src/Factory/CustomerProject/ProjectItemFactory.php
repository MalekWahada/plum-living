<?php

declare(strict_types=1);

namespace App\Factory\CustomerProject;

use App\Entity\CustomerProject\Project;
use App\Entity\CustomerProject\ProjectItem;
use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductGroup;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Exception\CustomerProject\DuplicatedProjectItemVariantException;
use App\Exception\CustomerProject\VariantsInconsistencyWithPlumScannerException;
use App\Model\CustomerProject\ProjectItemFormModel;
use App\Repository\Product\ProductGroupRepository;
use App\Repository\Product\ProductOptionValueRepository;
use App\Repository\Product\ProductVariantRepository;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ProjectItemFactory implements ProjectItemFactoryInterface
{
    private FactoryInterface $decoratedFactory;
    private ProjectItemVariantFactoryInterface $projectItemVariantFactory;
    private ProductVariantRepository $productVariantRepository;
    private ProductOptionValueRepository $productOptionValueRepository;
    private LoggerInterface $logger;
    private ProductGroupRepository $productGroupRepository;

    public function __construct(
        FactoryInterface $decoratedFactory,
        ProjectItemVariantFactoryInterface $projectItemVariantFactory,
        ProductVariantRepository $productVariantRepository,
        ProductOptionValueRepository $productOptionValueRepository,
        LoggerInterface $logger,
        ProductGroupRepository $productGroupRepository
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->projectItemVariantFactory = $projectItemVariantFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->logger = $logger;
        $this->productGroupRepository = $productGroupRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function createNew(): ProjectItem
    {
        /** @var ProjectItem $projectItem */
        $projectItem = $this->decoratedFactory->createNew();

        return $projectItem;
    }

    /**
     * {@inheritDoc}
     */
    public function createForProjectWithScannerDetails(Project $project, array $details): ProjectItem
    {
        $projectItem = $this->createNew();

        $projectItem->setPlumLabel($details['PlumLabel']);
        $projectItem->setIkeaSku($details['IkeaSKU']);
        $projectItem->setIkeaUnitPrice($details['IkeaUnitPrice']);
        $projectItem->setIkeaTotalPrice($details['IkeaTotalPrice']);
        $projectItem->setIkeaQuantity($details['IkeaQuantity']);
        $projectItem->setCabinetReference($details['CabinetReference']);
        $projectItem->setCurrency($details['Currency']);

        $uniqueDesignOptionValue = $this->productOptionValueRepository->findOneBy([
            'code' => ProductOptionValue::DESIGN_UNIQUE_CODE
        ]);

        foreach ($details['SyliusProductOptions'] as $variantDetails) {
            /** @var ProductVariant|null $productVariant */
            $productVariant = $this->productVariantRepository->findOneBy(["code" => $variantDetails["SKU"]]);

            if (null === $productVariant) {
                $this->logger->error(
                    "Couldn't find ProductVariant with given SyliusId : {$variantDetails['SyliusId']}"
                );

                throw new VariantsInconsistencyWithPlumScannerException('Variants Inconsistency with PlumScanner');
            }

            if ($projectItem->getVariants()->contains($productVariant)) {
                $this->logger->error(
                    "Duplicated ProductVariant with given SKU : {$variantDetails['SKU']}"
                );

                throw new DuplicatedProjectItemVariantException('Duplicated Product Variant');
            }

            $variant = $this->projectItemVariantFactory->createForProductVariantWithScannerDetails(
                $productVariant,
                $variantDetails
            );

            $productVariant = $variant->getProductVariant();

            if (($productVariant->getOptionValues()->contains($project->getDesign()) ||
                    $productVariant->getOptionValues()->contains($uniqueDesignOptionValue)) &&
                $productVariant->getOptionValues()->contains($project->getFinish()) &&
                $productVariant->getOptionValues()->contains($project->getColor())
            ) {
                $chosenVariant = $this->projectItemVariantFactory->createNew();

                $chosenVariant->setQuantity($variant->getQuantity());
                $chosenVariant->setProductVariant($productVariant);

                $projectItem->setChosenVariant($chosenVariant);
            }

            $projectItem->addVariant($variant);
        }

        return $projectItem;
    }

    public function createForScannerVariants(array $variants): ProjectItem
    {
        $projectItem = $this->createNew();

        foreach ($variants as $variant) {
            /** @var ProductVariant|null $productVariant */
            $productVariant = $this->productVariantRepository->findOneBy(["code" => $variant["SKU"]]);

            if (null === $productVariant) {
                continue;
            }

            $projectItem->addVariant(
                $this->projectItemVariantFactory->createForProductVariantWithScannerDetails(
                    $productVariant,
                    $variant
                )
            );
        }

        return $projectItem;
    }

    /**
     * {@inheritDoc}
     */
    public function createForProductVariants(array $variants): ProjectItem
    {
        $projectItem = $this->createNew();

        foreach ($variants as $variant) {
            $projectItemVariant = $this->projectItemVariantFactory->createNew();
            $projectItemVariant->setProductVariant($variant);
            $projectItemVariant->setQuantity(1);

            $projectItem->addVariant(
                $projectItemVariant
            );
        }

        return $projectItem;
    }

    /**
     * {@inheritDoc}
     */
    public function createCloneFromItem(ProjectItem $item): ProjectItem
    {
        $clone = $this->createNew();

        $clone->setPlumLabel($item->getPlumLabel());
        $clone->setIkeaSku($item->getIkeaSku());
        $clone->setIkeaUnitPrice($item->getIkeaUnitPrice());
        $clone->setIkeaQuantity($item->getIkeaQuantity());
        $clone->setCabinetReference($item->getCabinetReference());
        $clone->setIkeaTotalPrice($item->getIkeaTotalPrice());
        $clone->setCurrency($item->getCurrency());
        $clone->setComment($item->getComment());

        foreach ($item->getVariants() as $variant) {
            $cloneVariant = $this->projectItemVariantFactory->createCloneForVariant($variant);
            $clone->addVariant($cloneVariant);
        }

        if (null !== $item->getChosenVariant()) {
            $clone->setChosenVariant($this->projectItemVariantFactory->createCloneForVariant($item->getChosenVariant()));
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function createForFormModel(ProjectItemFormModel $formModel): ?ProjectItem
    {
        $projectItem = $formModel->getInternal();
        $isNewItem = null === $projectItem;

        // Case new item is added and JS autosave has not saved it yet.
        // JS is incrementing index when adding a new item. If the item index does not exist in items list it will be created here
        if ($isNewItem && null !== $formModel->getGroupId()) {
            /** @var ProductGroup|null $group */
            $group = $this->productGroupRepository->find($formModel->getGroupId());

            if (null === $group) {
                return null; // Delete the current item as group is not found
            }

            $projectItem = $this->createForProductGroup($group);
            $projectItem->setPlumLabel($group->getName());
            $projectItem->setGroupId((string)$group->getId()); // Group id must be stored for next requests if validation fails

            // Process VARIANT (no options) input
            if (!$projectItem->hasAnyVariantsWithOption() && null !== $formModel->getProductVariantId()) {
                $selectedVariant = $projectItem->getVariants()->filter(static function (ProjectItemVariant $variant) use ($formModel) {
                    return null !== $variant->getProductVariant() && $variant->getProductVariant()->getId() === (int) $formModel->getProductVariantId();
                })->first();
                $formModel->setVariant($selectedVariant !== false ? $selectedVariant : null);
            }
        } elseif ($isNewItem) {
            return null; // This new item has not been configured (no group selected). We delete it
        }

        $projectItem->setComment($formModel->getComment());

        if ($projectItem->hasAnyVariantsWithOption()) {
            $chosenVariant = $projectItem->getVariantByOptionValues($formModel->getDesign(), $formModel->getFinish(), $formModel->getColor(), $formModel->getHandleFinish(), $formModel->getTapFinish(), true);
        } else {
            $chosenVariant = $formModel->getVariant();
        }

        if (null !== $chosenVariant) {
            $chosenVariant->setQuantity($formModel->getQuantity());
        }
        $projectItem->setChosenVariant($chosenVariant); // Save project variant anyway. If it's null, it will emit an error from the ProductVariantExists validator.

        return $projectItem;
    }

    /**
     * {@inheritDoc}
     */
    public function createFormModelFromItem(ProjectItem $item): ProjectItemFormModel
    {
        $model = new ProjectItemFormModel();
        $model->init($item);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function createForProductGroup(ProductGroup $productGroup): ProjectItem
    {
        $item = $this->createNew();
        $item->setPlumLabel($productGroup->getName());
        $item->setCurrency('EUR');
        foreach ($productGroup->getProducts() as $product) {
            if (!$product->isEnabled()) {
                continue;
            }

            foreach ($product->getVariants() as $variant) {
                if (!$variant->isEnabled()) {
                    continue;
                }

                /** @var ProductVariantInterface $variant */
                $projectVariant = $this->projectItemVariantFactory->createForProductVariant($variant);
                $projectVariant->setProjectItem($item);
                $item->addVariant($projectVariant);
            }
        }
        return $item;
    }
}
