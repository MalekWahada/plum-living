<?php

declare(strict_types=1);

namespace App\Import\Processor;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\ProductVariant\ProductVariantPriceAdapter;
use App\Import\ResourceImporterValidationTrait;
use App\Provider\ImportExport\LocalizedFieldsProvider;
use App\Repository\Product\ProductOptionValueRepository;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductVariantRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingCategoryRepository;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Factory\ProductVariantFactory;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function explode;

final class ProductVariantProcessor implements ResourceProcessorInterface
{
    use ResourceImporterValidationTrait;

    private ProductVariantFactory $productVariantFactory;
    private FactoryInterface $channelPricingFactory;
    private ProductVariantRepository $productVariantRepository;
    private ProductRepository $productRepository;
    private ProductOptionValueRepository $productOptionValueRepository;
    private RepositoryInterface $channelPricingRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private ShippingCategoryRepository $shippingCategoryRepository;
    private LoggerInterface $csvImportLogger;
    private LocalizedFieldsProvider $fieldsProvider;

    private array $headerKeys;
    private const MAX_PROCESSING_TIME = 1800; // 30 minutes

    public function __construct(
        ProductVariantFactory $productVariantFactory,
        FactoryInterface $channelPricingFactory,
        ProductVariantRepository $productVariantRepository,
        ProductRepository $productRepository,
        ProductOptionValueRepository $productOptionValueRepository,
        RepositoryInterface $channelPricingRepository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager,
        ShippingCategoryRepository $shippingCategoryRepository,
        ValidatorInterface $validator,
        LoggerInterface $csvImportLogger,
        LocalizedFieldsProvider $fieldsProvider,
        array $headerKeys
    ) {
        $this->productVariantFactory = $productVariantFactory;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->productRepository = $productRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->metadataValidator = $metadataValidator;
        $this->entityManager = $entityManager;
        $this->headerKeys = $headerKeys;
        $this->validator = $validator;
        $this->shippingCategoryRepository = $shippingCategoryRepository;
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

        $localizedFields = $this->fieldsProvider->extractLocalizedFields(array_keys($data));
        if (empty($data['Code']) || !isset($localizedFields['Name'])) { // Mandatory fields
            throw new ItemIncompleteException('Code and Name are mandatory fields');
        }

        /** @var ProductVariant $productVariant */
        $productVariant = $this->getProductVariant($data['Code']);

        $this->setProduct($productVariant, $data);

        if (null === $productVariant->getProduct()) {
            return;
        }

        $this->setDetails($productVariant, $data, $localizedFields);
        $this->setOptions($productVariant, $data);
        $this->setPricing($productVariant, $data);
        $this->setDelivery($productVariant, $data);

        $this->validate($productVariant);
        $this->entityManager->persist($productVariant);
    }

    private function getProductVariant(string $code): ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $code]);
        if (null === $productVariant) {
            /** @var ProductVariantInterface $productVariant */
            $productVariant = $this->productVariantFactory->createNew();
            $productVariant->setCode($code);
            $this->csvImportLogger->info(sprintf('[PRODUCT-VARIANT] Creating new product variant "%s"', $code));
        } else {
            $this->csvImportLogger->info(sprintf('[PRODUCT-VARIANT] Updating existing product variant "%s"', $code));
        }

        return $productVariant;
    }

    private function setDetails(ProductVariantInterface $productVariant, array $data, array $localizedFields): void
    {
        // Set Name for all locales
        foreach ($localizedFields['Name'] as $locale => $localizedFieldName) {
            $productVariant->setFallbackLocale($locale); // Set fallback locale. In case of missing translation, it will be created automatically.
            $translation = $productVariant->getTranslation($locale);
            $translation->setName((string)substr($data[$localizedFieldName], 0, 255));
        }

        $productVariant->setWidth(!empty($data['Width']) ? (double)$data['Width'] : null);
        $productVariant->setHeight(!empty($data['Height']) ? (double)$data['Height']: null);
        $productVariant->setDepth(!empty($data['Depth']) ? (double)$data['Depth'] : null);
        $productVariant->setWeight(!empty($data['Weight']) ?(double)$data['Weight'] : null);
        $productVariant->setEnabled((bool)$data['Enabled']);
        $productVariant->setOnHand((int)$data['OnHand']);
        $productVariant->setOnHold((int)$data['OnHold']);
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setProduct(ProductVariantInterface $productVariant, array $data): void
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $data['ProductCode']]);

        if (null === $product) {
            $this->csvImportLogger->error(sprintf('[PRODUCT-VARIANT] Product with code "%s" not found', $data['ProductCode']));
            throw new ItemIncompleteException('Product with code ' . $data['ProductCode'] . ' does not exist');
        }

        $productVariant->setProduct($product);
    }

    private function setOptions(ProductVariantInterface $productVariant, array $data): void
    {
        $this->setOption($productVariant, $data, ucfirst(ProductOption::PRODUCT_OPTION_DESIGN), ProductOption::PRODUCT_OPTION_DESIGN);
        $this->setOption($productVariant, $data, ucfirst(ProductOption::PRODUCT_OPTION_FINISH), ProductOption::PRODUCT_OPTION_FINISH);
        $this->setOption($productVariant, $data, ucfirst(ProductOption::PRODUCT_OPTION_COLOR), ProductOption::PRODUCT_OPTION_COLOR);
        $this->setOption($productVariant, $data, 'DesignHandle', ProductOption::PRODUCT_HANDLE_OPTION_DESIGN); // Custom option name to avoid underscore in column
        $this->setOption($productVariant, $data, 'FinishHandle', ProductOption::PRODUCT_HANDLE_OPTION_FINISH);
        $this->setOption($productVariant, $data, 'DesignTap', ProductOption::PRODUCT_TAP_OPTION_DESIGN);
        $this->setOption($productVariant, $data, 'FinishTap', ProductOption::PRODUCT_TAP_OPTION_FINISH);
    }

    private function setOption(ProductVariantInterface $productVariant, array $data, string $optionName, string $optionCode): void
    {
        /** @var ProductOptionValue|null $optionValue */
        $optionValue = $this->productOptionValueRepository->findOneByCodeAndOptionCode(
            $data[$optionName],
            $optionCode
        );

        if (null === $optionValue) {
            return;
        }

        /** @var Collection|ProductOptionValue[] $optionValues */
        $optionValues = $productVariant->getOptionValues();

        $optionValueFilter = $optionValues->filter(function (ProductOptionValue $optionValue) use ($optionCode) {
            return null !== $optionValue->getOption() && $optionValue->getOption()->getCode() === $optionCode;
        });

        if (false !== $optionValueFilter->first()) {
            $productVariant->removeOptionValue($optionValueFilter->first());
        }

        $productVariant->addOptionValue($optionValue);
    }

    private function setPricing(ProductVariantInterface $productVariant, array $data): void
    {
        $channels = explode('|', $data['Channels']);
        foreach ($channels as $channelCode) {
            /** @var ChannelPricingInterface|null $channelPricing */
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channelCode,
                'productVariant' => $productVariant,
            ]);

            if (null === $channelPricing) {
                /** @var ChannelPricingInterface $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channelCode);
                $productVariant->addChannelPricing($channelPricing);
            }

            $channelPricing->setPrice(
                (int)((float)$data['Price'] * ProductVariantPriceAdapter::PRICE_PRECISION)
            );
        }
    }

    private function setDelivery(ProductVariant $productVariant, array $data): void
    {
        $productVariant->setDeliveryCalculationMode((string)$data['DeliveryCalculationMode']);
        $productVariant->setMinDayDelivery((int)$data['MinDayDelivery']);
        $productVariant->setMaxDayDelivery((int)$data['MaxDayDelivery']);
        $productVariant->setShippingCategory(
            $this->shippingCategoryRepository->findOneBy(['code' => $data['DeliveryCategory']])
        );
    }
}
