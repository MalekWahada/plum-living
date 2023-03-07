<?php

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Product\ProductCompleteInfo;
use App\Exception\Translation\EmptyTranslationBagException;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBag;
use App\Model\Translation\TranslationTaskResult;
use App\Translation\Client\TranslatorClientInterface;
use Psr\Log\LoggerInterface;

class ProductCompleteInfoTranslationTask extends TranslationTask
{
    private ProductCompleteInfoTranslationExtractionTool $extractionTool;

    public function __construct(
        ProductCompleteInfoTranslationExtractionTool $extractionTool,
        TranslatorClientInterface                    $translatorClient,
        LoggerInterface                              $translationLogger,
        bool                                         $lokalizeFechOnlyVerifiedKeys
    ) {
        parent::__construct($translatorClient, $translationLogger, $lokalizeFechOnlyVerifiedKeys);
        $this->extractionTool = $extractionTool;

        $this->translatorClient->setProject(TranslatorClientInterface::PRODUCT_COMPLETE_INFO_PROJECT);
    }

    /**
     * @param array|ProductCompleteInfo[] $objects
     * @return TranslationTaskResult
     */
    public function bulkPublish(array $objects): TranslationTaskResult
    {
        $result = new TranslationTaskResult();
        $bag = new TranslationBag();

        try {
            foreach ($objects as $completeInfo) {
                if (null === $product = $completeInfo->getProduct()) {
                    $this->logger->error('[PRODUCT-COMPLETE-INFO-TRANSLATION]: Product is null');
                    $result->addError(new TranslationException('Product is null'));

                    return $result;
                }

                $this->logger->info(sprintf('[PRODUCT-COMPLETE-INFO-TRANSLATION] Pushing translations for product %s (#%d, complete info #%d)', $product->getCode(), $product->getId(), $completeInfo->getId()));
                $this->extractionTool->extractTranslations($bag, $completeInfo);
            }

            // Check empty bag
            if ($bag->isEmpty()) {
                throw new EmptyTranslationBagException();
            }

            $this->translatorClient->publishKeys($bag);

            if (!$bag->hasErrors()) {
                $result->setSucceeded(sizeof($bag->getKeysNames(true)));
                $this->logger->info(sprintf('[PRODUCT-COMPLETE-INFO-TRANSLATION] Pushing translations succeeded (got %d keys)', sizeof($bag->getKeysNames(true))));
            }

            $result->addErrors($bag->getErrors()); // Pass bag errors to result
        } catch (TranslationException $e) {
            $result->addError($e);
        }

        return $result;
    }

    /**
     * @param array|ProductCompleteInfo[] $objects
     * @param string $locale
     * @return TranslationTaskResult
     */
    public function bulkFetch(array $objects, string $locale): TranslationTaskResult
    {
        $result = new TranslationTaskResult();
        $bag = new TranslationBag();
        $refBag = new TranslationBag();

        try {
            foreach ($objects as $completeInfo) {
                if (null === $product = $completeInfo->getProduct()) {
                    $this->logger->error('[PRODUCT-COMPLETE-INFO-TRANSLATION]: Product is null');
                    $result->addError(new TranslationException('Product is null'));

                    return $result;
                }

                $this->logger->info(sprintf('[PRODUCT-COMPLETE-INFO-TRANSLATION] Fetching translations for product %s (#%d, complete info #%d)', $product->getCode(), $product->getId(), $completeInfo->getId()));
                $this->extractionTool->extractTranslations($refBag, $completeInfo);
            }

            $this->translatorClient->fetchAllKeys($bag, $refBag->exportPrimaryTags(), null, $this->lokalizeFechOnlyVerifiedKeys);

            if (!$bag->hasErrors()) {
                $this->validateFetchedBag($bag, $refBag, $locale);

                foreach ($objects as $completeInfo) {
                    $this->extractionTool->applyTranslations($bag, $completeInfo, $locale);
                }

                $result->setSucceeded(sizeof($bag->getKeysNames(true)));
                $this->logger->info(sprintf('[PRODUCT-COMPLETE-INFO-TRANSLATION] Fetching translations succeeded (got %d keys)', sizeof($bag->getKeysNames(true))));
            }

            $result->addErrors($bag->getErrors()); // Pass bag errors to result
        } catch (TranslationException $e) {
            $result->addError($e);
            $this->logger->error(sprintf('[PRODUCT-COMPLETE-INFO-TRANSLATION] Error while fetching translations: %s', $e->getMessage()));
        }

        return $result;
    }
}
