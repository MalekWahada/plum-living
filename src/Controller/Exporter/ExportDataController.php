<?php

declare(strict_types=1);

namespace App\Controller\Exporter;

use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use Exception;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Metadata\Metadata;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Traversable;

/**
 * Overridden data exporter controller for product and product variant to allow grid filtering.
 */
final class ExportDataController
{
    public const EXPORTER_PRODUCT = 'sylius.product';
    public const EXPORTER_PRODUCT_VARIANT = 'sylius.product_variant';

    private array $resources;

    private RepositoryInterface $repository;

    private ServiceRegistryInterface $registry;

    private RequestConfigurationFactoryInterface $requestConfigurationFactory;

    private ResourcesCollectionProviderInterface $resourcesCollectionProvider;

    public function __construct(
        ServiceRegistryInterface $registry,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        ResourcesCollectionProviderInterface $resourcesCollectionProvider,
        RepositoryInterface $repository,
        array $resources
    ) {
        $this->registry = $registry;
        $this->requestConfigurationFactory = $requestConfigurationFactory;
        $this->resourcesCollectionProvider = $resourcesCollectionProvider;
        $this->repository = $repository;
        $this->resources = $resources;
    }

    /**
     * @throws Exception
     */
    public function exportAction(Request $request, string $resource, string $format): Response
    {
        $outputFilename = sprintf('%s-%s.%s', $resource, date('Y-m-d'), $format);

        return $this->exportData($request, $resource, $format, $outputFilename);
    }

    /**
     * @throws Exception
     */
    private function exportData(Request $request, string $exporter, string $format, string $outputFilename): Response
    {
        // check if product filter applied
        $productFilterApplied = ($request->query->count() > 0 && $exporter === self::EXPORTER_PRODUCT_VARIANT);

        if ($productFilterApplied) {
            $exporter = self::EXPORTER_PRODUCT;
            $request->attributes->set('resource', self::EXPORTER_PRODUCT);
            $request->attributes->set('_sylius', ['filterable' => true, 'grid' => 'sylius_admin_product']);
        }

        $metadata = Metadata::fromAliasAndConfiguration($exporter, $this->resources[$exporter]);
        $configuration = $this->requestConfigurationFactory->create($metadata, $request);

        $name = ExporterRegistry::buildServiceName(
            $productFilterApplied ? self::EXPORTER_PRODUCT_VARIANT : $exporter,
            $format
        );

        if (!$this->registry->has($name)) {
            throw new Exception(sprintf("No exporter found of type '%s' for format '%s'", $exporter, $format));
        }
        /** @var ResourceExporterInterface $service */
        $service = $this->registry->get($name);

        $resourcesToExport = $this->findResources($configuration, $this->repository);

        $service->export(
            $productFilterApplied ? $this->getResourceIds($resourcesToExport, true) : $this->getResourceIds($resourcesToExport)
        );

        $response = new Response($service->getExportedData());
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $outputFilename
        );

        $response->headers->set('Content-Type', 'application/' . $format);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @param ResourceGridView|array $resources
     * @param bool $switchToVariants
     *
     * @return int[]
     */
    private function getResourceIds($resources, bool $switchToVariants = false): array
    {
        if ($resources instanceof ResourceGridView
            && $resources->getData()->getAdapter() instanceof DoctrineORMAdapter) {
            $query = $resources->getData()->getAdapter()->getQuery()->setMaxResults(null);

            $variantsIds = [];
            if ($switchToVariants) {
                /** @var Product $product */
                foreach ($query->getResult() as $product) {
                    /** @var ProductVariant $variant */
                    foreach ($product->getVariants() as $variant) {
                        $variantsIds[] = $variant->getId();
                    }
                }
            }

            return $switchToVariants ? $variantsIds : array_column($query->getArrayResult(), 'id');
        }

        return array_map(static function (ResourceInterface $resource) {
            return $resource->getId();
        }, $this->getResources($resources));
    }

    /**
     * @param ResourceGridView|array $resources
     */
    private function getResources($resources): array
    {
        return is_array($resources) ? $resources : $this->getResourcesItems($resources);
    }

    private function getResourcesItems(ResourceGridView $resources): array
    {
        $data = $resources->getData();

        if (is_array($data)) {
            return $data;
        }

        $currentPageResults = $data->getCurrentPageResults();
        \assert($currentPageResults instanceof Traversable);

        if ($data instanceof Pagerfanta) {
            $results = [];

            for ($i = 0; $i < $data->getNbPages(); ++$i) {
                $data->setCurrentPage($i + 1);
                $results = array_merge($results, iterator_to_array($currentPageResults));
            }

            return $results;
        }

        return [];
    }

    /**
     * @return ResourceGridView|array
     */
    private function findResources(RequestConfiguration $configuration, RepositoryInterface $repository)
    {
        return $this->resourcesCollectionProvider->get($configuration, $repository);
    }
}
