<?php

declare(strict_types=1);

namespace App\Import\Processor;

use App\Entity\ProductIkea\ProductIkea;
use App\Entity\ProductIkea\ProductIkeaChannelPricing;
use App\Entity\ProductIkea\ProductIkeaImage;
use App\Entity\ProductIkea\ProductIkeaTranslation;
use App\Erp\Adapters\ProductVariant\ProductVariantPriceAdapter;
use App\Import\ResourceImporterValidationTrait;
use App\Repository\ProductIkea\ProductIkeaChannelPricingRepository;
use App\Repository\ProductIkea\ProductIkeaImageRepository;
use App\Repository\ProductIkea\ProductIkeaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function assert;

final class ProductIkeaProcessor implements ResourceProcessorInterface
{
    use ResourceImporterValidationTrait;

    public const BASE_URL = 'https://websiteikeaassets.blob.core.windows.net';

    private FactoryInterface $productIkeaFactory;
    private ProductIkeaRepository $productIkeaRepository;
    private FactoryInterface $productIkeaImageFactory;
    private ProductIkeaImageRepository $productIkeaImageRepository;
    private FactoryInterface $productIkeaChannelPricingFactory;
    private ProductIkeaChannelPricingRepository $productIkeaChannelPricingRepository;
    private MetadataValidatorInterface $metadataValidator;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $csvImportLogger;
    private ValidatorInterface $validator;

    private array $headerKeys;
    private const MAX_PROCESSING_TIME = 1800; // 30 minutes

    public function __construct(
        FactoryInterface                    $productIkeaFactory,
        ProductIkeaRepository               $productIkeaRepository,
        FactoryInterface                    $productIkeaImageFactory,
        ProductIkeaImageRepository          $productIkeaImageRepository,
        FactoryInterface                    $productIkeaChannelPricingFactory,
        ProductIkeaChannelPricingRepository $productIkeaChannelPricingRepository,
        MetadataValidatorInterface          $metadataValidator,
        EntityManagerInterface              $entityManager,
        ValidatorInterface                  $validator,
        LoggerInterface                     $csvImportLogger,
        array                               $headerKeys
    ) {
        $this->productIkeaFactory                   = $productIkeaFactory;
        $this->productIkeaRepository                = $productIkeaRepository;
        $this->productIkeaImageFactory              = $productIkeaImageFactory;
        $this->productIkeaImageRepository           = $productIkeaImageRepository;
        $this->productIkeaChannelPricingFactory     = $productIkeaChannelPricingFactory;
        $this->productIkeaChannelPricingRepository  = $productIkeaChannelPricingRepository;
        $this->metadataValidator                    = $metadataValidator;
        $this->entityManager                        = $entityManager;
        $this->headerKeys                           = $headerKeys;
        $this->csvImportLogger                      = $csvImportLogger;
        $this->validator                            = $validator;
    }

    /**
     * {@inheritdoc}
     * @throws ItemIncompleteException
     * @throws Exception
     */
    public function process(array $data): void
    {
        // this script will exceed the default 30 seconds of max execution time with huge amount of data
        set_time_limit(self::MAX_PROCESSING_TIME);

        // headers validation
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        // reformat data
        $this->prepareData($data);

        // checks mandatory fields
        if (empty($data['Id']) || empty($data['ImageLink'])) {
            throw new ItemIncompleteException('Id and ImageLink are mandatory fields');
        }

        $id = $data['Id'] ?? null;

        /** @var ProductIkea|null $productIkea */
        $productIkea = $this->productIkeaRepository->find($id);

        if (null === $productIkea) {
            $productIkea = $this->productIkeaFactory->createNew();
            $this->csvImportLogger->info('[PRODUCT_IKEA] Creating new product Ikea');
        } else {
            $this->csvImportLogger->info(sprintf('[PRODUCT_IKEA] Updating product Ikea #%d', $id));
        }

        assert($productIkea instanceof ProductIkea);

        $this->setData($productIkea, $data);
        $this->setTranslations($productIkea, $data);
        $this->setChannelPricings($productIkea, $data);
        $this->setImage($productIkea, $data);

        // validate and save
        $this->validate($productIkea);
        $this->entityManager->persist($productIkea);
    }

    private function prepareData(array &$data): void
    {
        $data['Id'] = str_pad($data['Id'], 8, '0', STR_PAD_LEFT);
    }

    private function setData(ProductIkea $productIkea, array $data): void
    {
        if (!$productIkea->getCode()) {
            $productIkea->setCode($data['Id']);
        }
    }

    private function setChannelPricings(ProductIkea $productIkea, array $data): void
    {
        $codes = [
            'Price'   => 'PLUM_FR',
            'PriceBE' => 'PLUM_BE',
            'PriceGB' => 'PLUM_EU',
            'PriceDE' => 'PLUM_DE',
            'PriceNL' => 'PLUM_NL',
        ];
        foreach ($codes as $code => $channelCode) {
            if (!$data[$code]) {
                continue;
            }
            /** @var ProductIkeaChannelPricing|null $channelPricing */
            $channelPricing = $this->productIkeaChannelPricingRepository->findOneBy([
                'channelCode' => $channelCode,
                'productIkea' => $productIkea,
            ]);

            if (null === $channelPricing) {
                $channelPricing = $this->productIkeaChannelPricingFactory->createNew();
                $channelPricing->setChannelCode($channelCode);
                $productIkea->addChannelPricing($channelPricing);
            }

            $channelPricing->setPrice(
                (int) ((float) $data[$code] * ProductVariantPriceAdapter::PRICE_PRECISION)
            );
        }
    }

    private function setTranslations(ProductIkea $productIkea, array $data): void
    {
        $locales = [
            'TypeName'   => 'fr',
            'TypeNameEN' => 'en',
            'TypeNameDE' => 'de',
            'TypeNameNL' => 'nl',
        ];
        foreach ($locales as $field => $locale) {
            if (!$data[$field]) {
                continue;
            }
            /** @var ProductIkeaTranslation|null $translation */
            $translation = $productIkea->getTranslation($locale);
            $translation->setName((string) substr($data[$field], 0, 255));
        }
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setImage(ProductIkea $productIkea, array $data): void
    {
        if (!filter_var($data['ImageLink'] ?? null, FILTER_VALIDATE_URL)) {
            return;
        }
        $directory = self::BASE_URL . '/size4/' . substr($productIkea->getCode(), 0, 3) . '/';
        $path = $directory . $productIkea->getCode() . '.jpg';
        if (null === $productIkea->getImage()) {
            /** @var ProductIkeaImage $image */
            $image = $this->productIkeaImageFactory->createNew();
            $productIkea->setImage($image);
        }
        // Update path for already uploaded image
        $productIkea->getImage()->setPath($path);
    }
}
