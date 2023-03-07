<?php

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Page\Page;
use App\Exception\Translation\EmptyTranslationBagException;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBag;
use App\Model\Translation\TranslationTaskResult;
use App\Translation\Client\TranslatorClientInterface;
use Psr\Log\LoggerInterface;

class PageTranslationTask extends TranslationTask
{
    private PageTranslationExtractionTool $extractionTool;

    public function __construct(
        PageTranslationExtractionTool $extractionTool,
        TranslatorClientInterface     $translatorClient,
        LoggerInterface               $translationLogger,
        bool                          $lokalizeFechOnlyVerifiedKeys
    ) {
        parent::__construct($translatorClient, $translationLogger, $lokalizeFechOnlyVerifiedKeys);
        $this->extractionTool = $extractionTool;
    }

    /**
     * @param array|Page[] $objects
     * @return TranslationTaskResult
     */
    public function bulkPublish(array $objects): TranslationTaskResult
    {
        $result = new TranslationTaskResult();
        $bag = new TranslationBag();

        try {
            foreach ($objects as $page) {
                $this->logger->info(sprintf('[PAGE-TRANSLATION] Pushing translations for page %s (#%d)', $page->getCode(), $page->getId()));
                $this->extractionTool->extractReferenceLocaleTranslations($bag, $page);
            }

            // Check empty bag
            if ($bag->isEmpty()) {
                throw new EmptyTranslationBagException();
            }

            $this->translatorClient->publishKeys($bag);

            if (!$bag->hasErrors()) {
                $result->setSucceeded(sizeof($bag->getKeysNames(true)));
                $this->logger->info(sprintf('[PAGE-TRANSLATION] Pushing translations succeeded (got %d keys)', sizeof($bag->getKeysNames(true))));
            }

            $result->addErrors($bag->getErrors()); // Pass bag errors to result
        } catch (TranslationException $e) {
            $result->addError($e);
        }

        return $result;
    }

    /**
     * @param array|Page[] $objects
     * @param string $locale
     * @return TranslationTaskResult
     */
    public function bulkFetch(array $objects, string $locale): TranslationTaskResult
    {
        $result = new TranslationTaskResult();
        $bag = new TranslationBag();
        $refBag = new TranslationBag();

        try {
            foreach ($objects as $page) {
                $this->logger->info(sprintf('[PAGE-TRANSLATION] Fetching translations for page %s (#%d)', $page->getCode(), $page->getId()));
                $this->extractionTool->extractReferenceLocaleTranslations($refBag, $page);
            }

            $this->translatorClient->fetchAllKeys($bag, $refBag->exportPrimaryTags(), null, $this->lokalizeFechOnlyVerifiedKeys);

            if (!$bag->hasErrors()) {
                $this->validateFetchedBag($bag, $refBag, $locale);

                foreach ($objects as $page) {
                    $this->extractionTool->applyTranslations($bag, $page, $locale);
                }

                $result->setSucceeded(sizeof($bag->getKeysNames(true)));
                $this->logger->info(sprintf('[PAGE-TRANSLATION] Fetching translations succeeded (got %d keys)', sizeof($bag->getKeysNames(true))));
            }

            $result->addErrors($bag->getErrors()); // Pass bag errors to result
        } catch (TranslationException $e) {
            $result->addError($e);
            $this->logger->error(sprintf('[PAGE-TRANSLATION] Error while fetching translations: %s', $e->getMessage()));
        }

        return $result;
    }
}
