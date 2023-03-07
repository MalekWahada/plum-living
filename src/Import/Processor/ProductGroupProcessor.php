<?php

declare(strict_types=1);

namespace App\Import\Processor;

use App\Entity\Product\Product;
use App\Entity\Product\ProductGroup;
use App\Import\ResourceImporterValidationTrait;
use App\Provider\ImportExport\LocalizedFieldsProvider;
use App\Repository\Product\ProductGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductGroupProcessor implements ResourceProcessorInterface
{
    use ResourceImporterValidationTrait;

    private MetadataValidatorInterface $metadataValidator;
    private FactoryInterface $productGroupFactory;
    private ProductGroupRepository $productGroupRepository;
    private TaxonRepositoryInterface $taxonRepository;
    private ProductRepositoryInterface $productRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $csvImportLogger;
    private LocalizedFieldsProvider $fieldsProvider;

    private array $headerKeys;
    private const MAX_PROCESSING_TIME = 1800; // 30 minutes

    public function __construct(
        MetadataValidatorInterface $metadataValidator,
        FactoryInterface           $productGroupFactory,
        ProductGroupRepository     $productGroupRepository,
        TaxonRepositoryInterface   $taxonRepository,
        ProductRepositoryInterface $productRepository,
        EntityManagerInterface     $entityManager,
        ValidatorInterface         $validator,
        LoggerInterface            $csvImportLogger,
        LocalizedFieldsProvider    $fieldsProvider,
        array                      $headerKeys
    ) {
        $this->metadataValidator = $metadataValidator;
        $this->productGroupFactory = $productGroupFactory;
        $this->productGroupRepository = $productGroupRepository;
        $this->taxonRepository = $taxonRepository;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->headerKeys = $headerKeys;
        $this->csvImportLogger = $csvImportLogger;
        $this->fieldsProvider = $fieldsProvider;
    }

    /**
     * {@inheritdoc}
     * @throws ItemIncompleteException
     */
    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        // this script will exceed the default 30 seconds of max execution time with huge amount of data
        set_time_limit(self::MAX_PROCESSING_TIME);

        $code = $data['Code'];
        $localizedFields = $this->fieldsProvider->extractLocalizedFields(array_keys($data));
        if (empty($data['Code']) || empty($data['MainTaxon']) || !isset($localizedFields['Name'])) { // Mandatory fields
            throw new ItemIncompleteException('Code, MainTaxon and Name are mandatory fields');
        }

        /** @var ProductGroup|null $productGroup */
        $productGroup = $this->productGroupRepository->findOneBy(['code' => $code]);

        if (null === $productGroup) {
            /** @var ProductGroup $productGroup */
            $productGroup = $this->productGroupFactory->createNew();
            $productGroup->setCode(trim($code));
            $this->csvImportLogger->info(sprintf('[PRODUCT-GROUP] Creating new product group "%s"', $code));
        } else {
            $this->csvImportLogger->info(sprintf('[PRODUCT-GROUP] Updating existing product group "%s"', $code));
        }

        $this->setData($productGroup, $data, $localizedFields);
        $this->setProducts($productGroup, $data);

        $this->validate($productGroup);
        $this->entityManager->persist($productGroup);
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setData(ProductGroup $productGroup, array $data, array $localizedFields): void
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $data['MainTaxon']]);
        if (null === $taxon) {
            throw new ItemIncompleteException(sprintf('MainTaxon "%s" not found', $data['MainTaxon']));
        }
        $productGroup->setMainTaxon($taxon);

        // Set Name for all locales
        foreach ($localizedFields['Name'] as $locale => $localizedFieldName) {
            $productGroup->setFallbackLocale($locale); // Set fallback locale. In case of missing translation, it will be created automatically.
            $translation = $productGroup->getTranslation($locale);
            $translation->setName((string)substr($data[$localizedFieldName], 0, 255));
        }
        $productGroup->setPosition((int)$data['Position']);
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setProducts(ProductGroup $productGroup, array $data): void
    {
        // Remove previous products
        foreach ($productGroup->getProducts() as $product) {
            $productGroup->removeProduct($product);
        }

        if (empty($data['Products'])) {
            return;
        }

        $splitProducts = explode('|', $data['Products']);
        foreach ($splitProducts as $code) {
            if (empty($code)) {
                continue;
            }

            /** @var Product|null $product */
            $product = $this->productRepository->findOneBy(['code' => $code]);
            if (null === $product) {
                throw new ItemIncompleteException(sprintf('Product "%s" not found', $code));
            }
            $productGroup->addProduct($product);
        }
    }
}
