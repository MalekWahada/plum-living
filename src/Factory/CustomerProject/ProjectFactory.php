<?php

declare(strict_types=1);

namespace App\Factory\CustomerProject;

use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use App\Exception\CustomerProject\DuplicatedProjectItemVariantException;
use App\Exception\CustomerProject\VariantsInconsistencyWithPlumScannerException;
use App\Repository\CustomerProject\ProjectRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectFactory implements ProjectFactoryInterface
{
    private FactoryInterface $decoratedFactory;
    private ProjectItemFactoryInterface $projectItemFactory;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;
    private ProjectRepository $projectRepository;

    public function __construct(
        FactoryInterface $decoratedFactory,
        ProjectItemFactoryInterface $projectItemFactory,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        ProjectRepository $projectRepository
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->projectItemFactory = $projectItemFactory;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->projectRepository = $projectRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function createNew(): Project
    {
        /** @var Project $project */
        $project = $this->decoratedFactory->createNew();
        try {
            $project->setToken(Uuid::uuid4()->toString());
        } catch (\Exception $exception) {
            throw new \RuntimeException('Unable to generate a unique token for the project.', 0, $exception);
        }
        return $project;
    }

    /**
     * {@inheritDoc}
     */
    public function createForCustomerWithOptionsAndScannerDetails(
        Customer $customer,
        ?Taxon $facadeType,
        ?ProductOptionValue $designOption,
        ?ProductOptionValue $finishOption,
        ?ProductOptionValue $colorOption,
        array $details
    ): ?Project {
        if (!array_key_exists('scannerTransferEmail', $details)) {
            return null;
        }
        $project = $this->createForCustomerWithOptionsAndPlumScannerId(
            $customer,
            $facadeType,
            $designOption,
            $finishOption,
            $colorOption,
            $details
        );

        if (null !== $project) {
            $project->setScannerTransferEmail($details['scannerTransferEmail']);
        }

        return $project;
    }

    /**
     * {@inheritDoc}
     */
    public function createForCustomerWithOptionsAndPlumScannerId(
        Customer $customer,
        ?Taxon $facadeType,
        ?ProductOptionValue $designOption,
        ?ProductOptionValue $finishOption,
        ?ProductOptionValue $colorOption,
        array $details
    ): ?Project {
        if (!array_key_exists('scannerProjectId', $details)) {
            return null;
        }

        $project = $this->createNew();

        $project->setName(sprintf(
            '%s #%d',
            $this->translator->trans('app.ui.generic.project'),
            $this->projectRepository->getLastId() + 1
        ));
        $project->setCustomer($customer);
        $project->setScannerProjectId($details['scannerProjectId']);
        // Use the same token as the scanner id for the project
        if (isset($details['scannerProjectId'])) {
            $project->setToken($details['scannerProjectId']);
        }
        $project->setFacade($facadeType);
        $project->setDesign($designOption);
        $project->setFinish($finishOption);
        $project->setColor($colorOption);

        return $project;
    }
    /**
     * {@inheritDoc}
     */
    public function bindItems(Project $project, array $items): Project
    {
        foreach ($items as $item) {
            try {
                $projectItem = $this->projectItemFactory->createForProjectWithScannerDetails($project, $item);
            } catch (DuplicatedProjectItemVariantException $exception) {
                throw new DuplicatedProjectItemVariantException(
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception
                );
            } catch (VariantsInconsistencyWithPlumScannerException $exception) {
                throw new VariantsInconsistencyWithPlumScannerException(
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception
                );
            }

            if (0 === $projectItem->getVariants()->count()) {
                continue;
            }

            $project->addItem($projectItem);
        }

        if (0 === $project->getItems()->count()) {
            $this->logger->error(
                "0 items fetched for project with scannerProjectId : {$project->getScannerProjectId()}"
            );
            throw new VariantsInconsistencyWithPlumScannerException('Variants Inconsistency with PlumScanner');
        }

        // mark project as fetched
        $project->setScannerFetched(true);

        return $project;
    }

    public function createItemsForClonedProject(Project $project, Project $clone): Project
    {
        foreach ($project->getItems() as $item) {
            $cloneItem = $this->projectItemFactory->createCloneFromItem($item);
            $clone->addItem($cloneItem);
        }

        return $clone;
    }

    public function createForClone(Project $project): Project
    {
        $clone = $this->createNew();
        $clone->setName(sprintf(
            '%s #%d',
            $this->translator->trans('app.ui.generic.project'),
            $this->projectRepository->getLastId() + 1
        ));
        $clone->setCustomer($project->getCustomer());
        $clone->setFacade($project->getFacade());
        $clone->setDesign($project->getDesign());
        $clone->setFinish($project->getFinish());
        $clone->setColor($project->getColor());

        return $clone;
    }
}
