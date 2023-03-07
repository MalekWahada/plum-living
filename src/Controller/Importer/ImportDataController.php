<?php

declare(strict_types=1);

namespace App\Controller\Importer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Form\ImportType;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResult;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class ImportDataController extends AbstractController
{
    private FlashBagInterface $flashBag;
    private ServiceRegistryInterface $registry;
    private FormFactoryInterface $formFactory;

    private const IMPORTER_PRODUCT = 'product';
    private const IMPORTER_PRODUCT_VARIANT = 'product_variant';
    private const IMPORTER_PRODUCT_IKEA = 'product_ikea';
    private const LOCAL_RESOURCES = ['combination', 'product_group', 'product_ikea'];

    public function __construct(
        ServiceRegistryInterface $registry,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory
    ) {
        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
    }

    public function importFormAction(Request $request): Response
    {
        $importer = $request->attributes->get('resource');
        $importer = (in_array($importer, self::LOCAL_RESOURCES, true) ? 'app.' : '') . $importer; // Prefix 'app.' is missing for local resources

        // return empty response if resource is product_variant
        if (self::IMPORTER_PRODUCT_VARIANT === $importer) {
            return new Response();
        }

        if (self::IMPORTER_PRODUCT === $importer) {
            $productsImportForm = $this->getForm(self::IMPORTER_PRODUCT);
            $productVariantsImportForm = $this->getForm(self::IMPORTER_PRODUCT_VARIANT);

            return $this->render(
                'bundles/FOSSyliusImportExportPlugin/Crud/products_import_form.html.twig',
                [
                    'productsImportForm' => $productsImportForm->createView(),
                    'productVariantsImportForm' => $productVariantsImportForm->createView(),
                    'productResource' => self::IMPORTER_PRODUCT,
                    'productVariantResource' => self::IMPORTER_PRODUCT_VARIANT
                ]
            );
        }

        $form = $this->getForm($importer);

        return $this->render(
            '@FOSSyliusImportExportPlugin/Crud/import_form.html.twig',
            [
                'form' => $form->createView(),
                'resource' => $importer,
            ]
        );
    }

    public function importAction(Request $request): RedirectResponse
    {
        $importer = $request->attributes->get('resource');
        $form = $this->getForm($importer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->importData($importer, $form);
            } catch (\Throwable $exception) {
                $this->flashBag->add('error', $exception->getMessage());
            }
        }
        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    private function getForm(string $importerType): FormInterface
    {
        return $this->formFactory->create(ImportType::class, null, ['importer_type' => $importerType]);
    }

    /**
     * @throws ImporterException
     */
    private function importData(string $importer, FormInterface $form): void
    {
        $format = $form->get('format')->getData();
        $name = ImporterRegistry::buildServiceName($importer, $format);
        if (!$this->registry->has($name)) {
            $message = sprintf("No importer found of type '%s' for format '%s'", $importer, $format);

            throw new ImporterException($message);
        }

        /** @var UploadedFile|null $file */
        $file = $form->get('import-data')->getData();
        /** @var ImporterInterface $service */
        $service = $this->registry->get($name);

        if (null === $file) {
            throw new ImporterException('No file selected');
        }

        $path = $file->getRealPath();

        if (false === $path) {
            throw new ImporterException(sprintf('File %s could not be loaded', $file->getClientOriginalName()));
        }

        /** @var ImporterResult $result */
        $result = $service->import($path);
        $failed = count($result->getFailedRows()) > 0;
        $stopOnFailure = $this->getParameter('sylius.importer.stop_on_failure');

        $message = sprintf(
            'Import via %s importer (Time taken in ms: %s, %s %s, Skipped %s, Failed %s).%s',
            $name,
            $result->getDuration(),
            $failed && $stopOnFailure ? 'Valid' : 'Imported',
            count($result->getSuccessRows()),
            count($result->getSkippedRows()),
            count($result->getFailedRows()),
            $failed && $stopOnFailure ? ' No data has been imported. The transaction has been rolled back.' : ''
        );

        $this->flashBag->add($failed ? 'error' : 'success', $message);

        if ($result->getMessage() !== null) {
            throw new ImporterException($result->getMessage());
        }
    }
}
