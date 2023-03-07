<?php

declare(strict_types=1);

namespace App\Import\Processor;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Tunnel\Shopping\Combination;
use App\Entity\Tunnel\Shopping\CombinationImage;
use App\Import\ResourceImporterValidationTrait;
use App\Repository\Product\ProductOptionValueRepository;
use App\Repository\Tunnel\Shopping\CombinationRepository;
use App\Uploader\ImageHttpUrlUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CombinationProcessor implements ResourceProcessorInterface
{
    use ResourceImporterValidationTrait;

    private FactoryInterface $combinationFactory;
    private FactoryInterface $combinationImageFactory;
    private CombinationRepository $combinationRepository;
    private MetadataValidatorInterface $metadataValidator;
    private TaxonRepositoryInterface $taxonRepository;
    private ProductOptionValueRepository $productOptionValueRepository;
    private EntityManagerInterface $entityManager;
    private ImageUploaderInterface $uploader;
    private ImageHttpUrlUploader $httpUploader;
    private LoggerInterface $csvImportLogger;

    private array $headerKeys;
    private const MAX_PROCESSING_TIME = 1800; // 30 minutes

    public function __construct(
        MetadataValidatorInterface $metadataValidator,
        FactoryInterface           $combinationFactory,
        FactoryInterface           $combinationImageFactory,
        CombinationRepository      $combinationRepository,
        TaxonRepositoryInterface   $taxonRepository,
        ProductOptionValueRepository $productOptionValueRepository,
        EntityManagerInterface     $entityManager,
        ImageUploaderInterface     $uploader,
        ImageHttpUrlUploader       $httpUploader,
        ValidatorInterface         $validator,
        LoggerInterface            $csvImportLogger,
        array                      $headerKeys
    ) {
        $this->combinationFactory = $combinationFactory;
        $this->combinationImageFactory = $combinationImageFactory;
        $this->combinationRepository = $combinationRepository;
        $this->metadataValidator = $metadataValidator;
        $this->taxonRepository = $taxonRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->entityManager = $entityManager;
        $this->uploader = $uploader;
        $this->httpUploader = $httpUploader;
        $this->validator = $validator;
        $this->headerKeys = $headerKeys;
        $this->csvImportLogger = $csvImportLogger;
    }

    /**
     * {@inheritdoc}
     * @throws ItemIncompleteException
     * @throws Exception
     */
    public function process(array $data): void
    {
        $this->metadataValidator->validateHeaders($this->headerKeys, $data);

        // this script will exceed the default 30 seconds of max execution time with huge amount of data
        set_time_limit(self::MAX_PROCESSING_TIME);

        $id = $data['Id'] ?? null;

        if (empty($data['FacadeType']) || empty($data['Image'])) { // Mandatory fields
            throw new ItemIncompleteException('FacadeType and Image are mandatory fields');
        }

        /** @var Combination|null $combination */
        $combination = $this->combinationRepository->find($id);

        if (null === $combination) {
            $combination = $this->combinationFactory->createNew();
            $this->csvImportLogger->info('[COMBINATION] Creating new combination');
        } else {
            $this->csvImportLogger->info(sprintf('[COMBINATION] Updating combination #%d', $id));
        }

        \assert($combination instanceof Combination);

        $this->setFacadeType($combination, $data);
        $this->setData($combination, $data);
        $this->setOptions($combination, $data);
        $this->setImage($combination, $data);

        $this->validate($combination);
        $this->entityManager->persist($combination);
    }

    /**
     * @throws Exception
     */
    private function setFacadeType(Combination $combination, array $data): void
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $data['FacadeType']]);
        if (null === $taxon) {
            throw new ItemIncompleteException(sprintf('FacadeType "%s" not found', $data['FacadeType']));
        }
        $combination->setFacadeType($taxon);
    }

    private function setData(Combination $combination, array $data): void
    {
        if (!empty($data['Recommendation'])) {
            $combination->setRecommendation($data['Recommendation']);
        }
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setImage(Combination $combination, array $data): void
    {
        if (null === $combination->getImage()) {
            /** @var CombinationImage $image */
            $image = $this->combinationImageFactory->createNew();
            $combination->setImage($image);
        }

        // Case HTTP Url: upload to temporary folder
        if (filter_var($data['Image'], FILTER_VALIDATE_URL)) {
            if (null === $file = $this->httpUploader->uploadToTmp($data['Image'])) {
                throw new ItemIncompleteException(sprintf('Image "%s" not found', $data['Image']));
            }
            $combination->getImage()->setFile($file);
            $this->uploader->upload($combination->getImage());
            return;
        }

        // Update path for already uploaded image
        $combination->getImage()->setPath($data['Image']);
    }

    /**
     * @throws ItemIncompleteException
     */
    private function setOptions(Combination $combination, array $data): void
    {
        if (!empty($data['Design'])) {
            $combination->setDesign($this->getProductOptionValue($data['Design'], ProductOption::PRODUCT_OPTION_DESIGN));
        }
        if (!empty($data['Finish'])) {
            $combination->setFinish($this->getProductOptionValue($data['Finish'], ProductOption::PRODUCT_OPTION_FINISH));
        }
        if (!empty($data['Color'])) {
            $combination->setColor($this->getProductOptionValue($data['Color'], ProductOption::PRODUCT_OPTION_COLOR));
        }
    }

    /**
     * @param string $code
     * @param string $optionCode
     * @return ProductOptionValue
     * @throws ItemIncompleteException
     */
    private function getProductOptionValue(string $code, string $optionCode): ProductOptionValue
    {
        $productOptionValue = $this->productOptionValueRepository->findOneByCodeAndOptionCode($code, $optionCode);

        if (null === $productOptionValue) {
            throw new ItemIncompleteException(sprintf('Can not find %s option value with code: "%s"', $optionCode, $code));
        }

        return $productOptionValue;
    }
}
