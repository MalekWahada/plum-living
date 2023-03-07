<?php

declare(strict_types=1);

namespace App\Import\Processor;

use App\Entity\Product\Product;
use App\Entity\Product\ProductGroup;
use App\Entity\Product\ProductTranslation;
use App\Import\ResourceImporterValidationTrait;
use App\Provider\ImportExport\LocalizedFieldsProvider;
use App\Provider\Translation\TranslationLocaleProvider;
use App\Transformer\MonsieurBiz\TextTransformer;
use App\Uploader\ImageHttpUrlUploader;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProviderInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductTaxonRepository;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function array_merge;
use function explode;

final class ProductProcessor implements ResourceProcessorInterface
{
    use ResourceImporterValidationTrait;

    private ChannelRepositoryInterface $channelRepository;
    private FactoryInterface $productTaxonFactory;
    private ProductTaxonRepository $productTaxonRepository;
    private FactoryInterface $productImageFactory;
    private ImageTypesProviderInterface $imageTypesProvider;
    private EntityManagerInterface $manager;
    private ?TransformerPoolInterface $transformerPool;
    private ProductFactoryInterface $resourceProductFactory;
    private ProductRepositoryInterface $productRepository;
    private TaxonRepositoryInterface $taxonRepository;
    private MetadataValidatorInterface $metadataValidator;
    private RepositoryInterface $productAttributeRepository;
    private FactoryInterface $productAttributeValueFactory;
    private RepositoryInterface $productVariantRepository;
    private RepositoryInterface $productGroupRepository;
    private AttributeCodesProviderInterface $attributeCodesProvider;
    private SlugGeneratorInterface $slugGenerator;
    private FactoryInterface $productVariantFactory;
    private TextTransformer $textTransformer;
    private ImageHttpUrlUploader $httpUploader;
    private ImageUploaderInterface $uploader;
    private LoggerInterface $csvImportLogger;
    private LocalizedFieldsProvider $fieldsProvider;
    private TranslationLocaleProvider $translationLocaleProvider;

    private array $headerKeys;
    private array $attrCode;

    private const MAX_PROCESSING_TIME = 1800; // 30 minutes

    public function __construct(
        ProductFactoryInterface $productFactory,
        ProductRepositoryInterface $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        MetadataValidatorInterface $metadataValidator,
        RepositoryInterface $productAttributeRepository,
        AttributeCodesProviderInterface $attributeCodesProvider,
        FactoryInterface $productAttributeValueFactory,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface            $productTaxonFactory,
        FactoryInterface            $productImageFactory,
        FactoryInterface            $productVariantFactory,
        ProductTaxonRepository      $productTaxonRepository,
        RepositoryInterface         $productVariantRepository,
        RepositoryInterface         $productGroupRepository,
        ImageTypesProviderInterface $imageTypesProvider,
        SlugGeneratorInterface      $slugGenerator,
        ?TransformerPoolInterface   $transformerPool,
        EntityManagerInterface      $manager,
        TextTransformer             $textTransformer,
        ImageUploaderInterface      $uploader,
        ImageHttpUrlUploader        $httpUploader,
        ValidatorInterface          $validator,
        LoggerInterface             $csvImportLogger,
        TranslationLocaleProvider   $translationLocaleProvider,
        LocalizedFieldsProvider     $fieldsProvider,
        array                       $headerKeys
    ) {
        $this->resourceProductFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->taxonRepository = $taxonRepository;
        $this->metadataValidator = $metadataValidator;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->slugGenerator = $slugGenerator;
        $this->transformerPool = $transformerPool;
        $this->channelRepository = $channelRepository;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->productGroupRepository = $productGroupRepository;
        $this->productImageFactory = $productImageFactory;
        $this->imageTypesProvider = $imageTypesProvider;
        $this->productVariantFactory = $productVariantFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->manager = $manager;
        $this->textTransformer = $textTransformer;
        $this->uploader = $uploader;
        $this->httpUploader = $httpUploader;
        $this->validator = $validator;
        $this->csvImportLogger = $csvImportLogger;
        $this->translationLocaleProvider = $translationLocaleProvider;
        $this->fieldsProvider = $fieldsProvider;
        $this->headerKeys = $headerKeys;
    }

    /**
     * {@inheritdoc}
     * @throws ItemIncompleteException
     */
    public function process(array $data): void
    {
        $this->attrCode = $this->attributeCodesProvider->getAttributeCodesList();
        $imageCode = $this->imageTypesProvider->getProductImagesCodesWithPrefixList();

        $this->headerKeys = array_merge($this->headerKeys, $this->attrCode);
        $this->headerKeys = array_merge($this->headerKeys, $imageCode);
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        // this script will exceed the default 30 seconds of max execution time with huge amount of data
        set_time_limit(self::MAX_PROCESSING_TIME);

        $localizedFields = $this->fieldsProvider->extractLocalizedFields(array_keys($data));
        if (empty($data['Code']) ||
            !isset(
                $localizedFields['Name'],
                $localizedFields['Description'],
                $localizedFields['ShortDescription'],
                $localizedFields['MetaDescription'],
                $localizedFields['MetaKeywords'],
                $localizedFields['DeliveryDescription']
            )
        ) { // Mandatory fields
            throw new ItemIncompleteException('Code, Name, Description, ShortDescription, MetaDescription, MetaKeywords and DeliveryDescription are mandatory fields');
        }
        $product = $this->getProduct($data['Code']);

        $this->setDetails($product, $data, $localizedFields);
        $this->setAttributesData($product, $data);
        $this->setMainTaxon($product, $data);
        $this->setTaxons($product, $data);
        $this->setChannel($product, $data);
        $this->setImage($product, $data);
        $this->setGroups($product, $data);

        $this->validate($product);
        $this->productRepository->add($product);
    }

    private function getProduct(string $code): Product
    {
        /** @var Product|null $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);
        if (null === $product) {
            /** @var Product $product */
            $product = $this->resourceProductFactory->createNew();
            $product->setCode($code);
            $this->csvImportLogger->info(sprintf('[PRODUCT] Creating new product "%s"', $code));
        } else {
            $this->csvImportLogger->info(sprintf('[PRODUCT] Updating existing product "%s"', $code));
        }

        return $product;
    }

    private function getProductVariant(string $code): ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $code]);
        if ($productVariant === null) {
            /** @var ProductVariantInterface $productVariant */
            $productVariant = $this->productVariantFactory->createNew();
            $productVariant->setCode($code);
        }

        return $productVariant;
    }

    private function setMainTaxon(ProductInterface $product, array $data): void
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $data['MainTaxon']]);
        if ($taxon === null) {
            return;
        }

        $product->setMainTaxon($taxon);

        $this->addTaxonToProduct($product, $data['MainTaxon']);
    }

    private function setTaxons(ProductInterface $product, array $data): void
    {
        $taxonCodes = explode('|', $data['Taxons']);
        foreach ($taxonCodes as $taxonCode) {
            if ($taxonCode !== $data['MainTaxon']) {
                $this->addTaxonToProduct($product, $taxonCode);
            }
        }
    }

    private function setAttributesData(ProductInterface $product, array $data): void
    {
        foreach ($this->attrCode as $attrCode) {
            // Update or create attribute value for each locales available
            foreach ($this->translationLocaleProvider->getDefinedLocalesCodesOrDefault() as $localeCode) {
                $attributeValue = $product->getAttributeByCodeAndLocale($attrCode, $localeCode);

                if (empty($data[$attrCode])) {
                    if ($attributeValue !== null) {
                        $product->removeAttribute($attributeValue);
                    }

                    continue;
                }

                if ($attributeValue !== null) {
                    if (null !== $this->transformerPool && is_string($data[$attrCode])) {
                        $data[$attrCode] = $this->transformerPool->handle(
                            $attributeValue->getType(),
                            $data[$attrCode]
                        );
                    }

                    $attributeValue->setValue($data[$attrCode]);

                    continue;
                }

                $this->createProductAttributeValue($product, $data, $attrCode, $localeCode);
            }
        }
    }

    private function setDetails(ProductInterface $product, array $data, array $localizedFields): void
    {
        $product->setEnabled((bool)$data['Enabled']);
        $product->setSlug($product->getSlug() ?: $this->slugGenerator->generate($product->getName()));

        // Set Name for all locales (Name_fr, Name_en, Name_de, ...)
        foreach ($localizedFields['Name'] as $locale => $localizedFieldName) {
            $product->setFallbackLocale($locale); // Set fallback locale. In case of missing translation, it will be created automatically.
            $translation = $product->getTranslation($locale);
            $translation->setName((string)substr($data[$localizedFieldName], 0, 255));
        }

        // Set Description for all locales
        foreach ($localizedFields['Description'] as $locale => $localizedFieldName) {
            $product->setFallbackLocale($locale);
            $translation = $product->getTranslation($locale);
            $translation->setDescription($data[$localizedFieldName]);
        }

        // Set ShortDescription for all locales
        foreach ($localizedFields['ShortDescription'] as $locale => $localizedFieldName) {
            $product->setFallbackLocale($locale);
            $translation = $product->getTranslation($locale);
            $translation->setShortDescription(substr($data[$localizedFieldName], 0, 255));
        }

        // Set MetaDescription for all locales
        foreach ($localizedFields['MetaDescription'] as $locale => $localizedFieldName) {
            $product->setFallbackLocale($locale);
            $translation = $product->getTranslation($locale);
            $translation->setMetaDescription(substr($data[$localizedFieldName], 0, 255));
        }

        // Set MetaKeywords for all locales
        foreach ($localizedFields['MetaKeywords'] as $locale => $localizedFieldName) {
            $product->setFallbackLocale($locale);
            $translation = $product->getTranslation($locale);
            $translation->setMetaKeywords(substr($data[$localizedFieldName], 0, 255));
        }

        // Set DeliveryDescription for all locales
        foreach ($localizedFields['DeliveryDescription'] as $locale => $localizedFieldName) {
            $product->setFallbackLocale($locale);
            /** @var ProductTranslation $translation */
            $translation = $product->getTranslation($locale);
            $translation->setDeliveryDescription($this->textTransformer->reverseTransform($data[$localizedFieldName]));
        }

        // Reset slug for all locales
        /** @var ProductTranslation $translation */
        foreach ($product->getTranslations() as $translation) {
            $translation->setSlug($product->getSlug() ?: $this->slugGenerator->generate($product->getName()));
        }
    }

    private function createProductAttributeValue(ProductInterface $product, array $data, string $attrCode, string $localeCode): void
    {
        /** @var ProductAttribute $productAttr */
        $productAttr = $this->productAttributeRepository->findOneBy(['code' => $attrCode]);
        /** @var ProductAttributeValueInterface $attr */
        $attr = $this->productAttributeValueFactory->createNew();
        $attr->setAttribute($productAttr);
        $attr->setProduct($product);
        $attr->setLocaleCode($product->getTranslation($localeCode)->getLocale());

        if (null !== $this->transformerPool && is_string($data[$attrCode])) {
            $data[$attrCode] = $this->transformerPool->handle($productAttr->getType(), $data[$attrCode]);
        }

        $attr->setValue($data[$attrCode]);
        $product->addAttribute($attr);
        $this->manager->persist($attr);
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setChannel(ProductInterface $product, array $data): void
    {
        $channels = explode('|', $data['Channels']);
        foreach ($channels as $channelCode) {
            /** @var ChannelInterface|null $channel */
            $channel = $this->channelRepository->findOneBy(['code' => $channelCode]);
            if ($channel === null) {
                throw new ItemIncompleteException(sprintf('Channel "%s" not found', $channelCode));
            }
            $product->addChannel($channel);
        }
    }

    private function addTaxonToProduct(ProductInterface $product, string $taxonCode): void
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        if ($taxon === null) {
            return;
        }

        $productTaxon = $this->productTaxonRepository->findOneByProductCodeAndTaxonCode(
            $product->getCode(),
            $taxon->getCode()
        );

        if (null !== $productTaxon) {
            return;
        }

        /** @var ProductTaxonInterface $productTaxon */
        $productTaxon = $this->productTaxonFactory->createNew();
        $productTaxon->setTaxon($taxon);
        $product->addProductTaxon($productTaxon);
    }

    private function setImage(ProductInterface $product, array $data): void
    {
        $productImageCodes = $this->imageTypesProvider->getProductImagesCodesList();
        foreach ($productImageCodes as $imageType) {
            $this->updateImage($product, $imageType, $data);
        }

        foreach ($this->imageTypesProvider->extractImageTypeFromImport(array_keys($data)) as $imageType) {
            $this->createImage($product, $productImageCodes, $imageType, $data);
        }
    }

    /**
     * Update image from import
     *
     * @param ProductInterface $product
     * @param string $imageType
     * @param array $data
     */
    private function updateImage(ProductInterface $product, string $imageType, array $data): void
    {
        $productImageByType = $product->getImagesByType($imageType);

        $this->removeOldImages($product, $productImageByType, $imageType, $data);

        if (empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType])) {
            return;
        }

        if (count($productImageByType) === 0) {
            /** @var ProductImageInterface $productImage */
            $productImage = $this->productImageFactory->createNew();
        } else {
            /** @var ProductImageInterface $productImage */
            $productImage = $productImageByType->first();
        }

        $productImage->setType($imageType);

        if (filter_var($data[ImageTypesProvider::IMAGES_PREFIX . $imageType], FILTER_VALIDATE_URL)) {
            $this->uploadHttpUrl($productImage, $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]);
        } else {
            $productImage->setPath($data[ImageTypesProvider::IMAGES_PREFIX . $imageType]);
        }

        $product->addImage($productImage);
    }

    /**
     * Create image if import has new one
     *
     * @param ProductInterface $product
     * @param array $productImageCodes
     * @param string $imageType
     * @param array $data
     */
    private function createImage(
        ProductInterface $product,
        array $productImageCodes,
        string $imageType,
        array $data
    ): void {
        if (empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType]) || in_array($imageType, $productImageCodes, true)) {
            return;
        }

        /** @var ProductImageInterface $productImage */
        $productImage = $this->productImageFactory->createNew();
        $productImage->setType($imageType);

        if (filter_var($data[ImageTypesProvider::IMAGES_PREFIX . $imageType], FILTER_VALIDATE_URL)) {
            $this->uploadHttpUrl($productImage, $data[ImageTypesProvider::IMAGES_PREFIX . $imageType]);
        } else {
            $productImage->setPath($data[ImageTypesProvider::IMAGES_PREFIX . $imageType]);
        }

        $product->addImage($productImage);
    }

    /**
     *  Remove old images if import is empty
     *
     * @param ProductInterface $product
     * @param Collection $productImageByType
     * @param string $imageType
     * @param array $data
     */
    private function removeOldImages(
        ProductInterface $product,
        Collection $productImageByType,
        string $imageType,
        array $data
    ): void {
        foreach ($productImageByType as $productImage) {
            if (empty($data[ImageTypesProvider::IMAGES_PREFIX . $imageType]) && $productImage !== null) {
                $product->removeImage($productImage);
            }
        }
    }

    private function uploadHttpUrl(ProductImageInterface $productImage, string $url): void
    {
        $file = $this->httpUploader->uploadToTmp($url);
        if (null !== $file) {
            // upload image
            $productImage->setFile($file);
            $this->uploader->upload($productImage);
        }
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setGroups(Product $product, array $data): void
    {
        // Remove previous groups
        foreach ($product->getGroups() as $group) {
            $product->removeGroup($group);
        }

        if (empty($data['Groups'])) {
            return;
        }

        $splitGroups = explode('|', $data['Groups']);
        foreach ($splitGroups as $code) {
            if (empty($code)) {
                continue;
            }

            /** @var ProductGroup|null $group */
            $group = $this->productGroupRepository->findOneBy(['code' => $code]);
            if (null === $group) {
                throw new ItemIncompleteException(sprintf('ProductGroup "%s" not found', $code));
            }

            $product->addGroup($group);
        }
    }
}
