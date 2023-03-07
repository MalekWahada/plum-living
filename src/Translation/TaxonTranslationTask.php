<?php

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Taxonomy\Taxon;
use App\Exception\Translation\EmptyTranslationBagException;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBag;
use App\Model\Translation\TranslationTaskResult;
use App\Translation\Client\TranslatorClientInterface;
use Psr\Log\LoggerInterface;

class TaxonTranslationTask extends TranslationTask
{
    private TaxonTranslationExtractionTool $extractionTool;

    public function __construct(
        TaxonTranslationExtractionTool $extractionTool,
        TranslatorClientInterface      $translatorClient,
        LoggerInterface                $translationLogger,
        bool                           $lokalizeFechOnlyVerifiedKeys
    ) {
        parent::__construct($translatorClient, $translationLogger, $lokalizeFechOnlyVerifiedKeys);
        $this->extractionTool = $extractionTool;

        $this->translatorClient->setProject(TranslatorClientInterface::TAXON_PROJECT);
    }

    /**
     * @param array|Taxon[] $objects
     * @return TranslationTaskResult
     */
    public function bulkPublish(array $objects): TranslationTaskResult
    {
        $result = new TranslationTaskResult();
        $bag = new TranslationBag();

        try {
            foreach ($objects as $taxon) {
                $this->logger->info(sprintf('[TAXON-TRANSLATION] Pushing translations for taxon %s (#%d)', $taxon->getCode(), $taxon->getId()));
                $this->extractionTool->extractTranslations($bag, $taxon);
            }

            // Check empty bag
            if ($bag->isEmpty()) {
                throw new EmptyTranslationBagException();
            }

            $this->translatorClient->publishKeys($bag);

            if (!$bag->hasErrors()) {
                $result->setSucceeded(sizeof($bag->getKeysNames(true)));
                $this->logger->info(sprintf('[TAXON-TRANSLATION] Pushing translations succeeded (got %d keys)', sizeof($bag->getKeysNames(true))));
            }

            $result->addErrors($bag->getErrors()); // Pass bag errors to result
        } catch (TranslationException $e) {
            $result->addError($e);
        }

        return $result;
    }

    /**
     * @param array|Taxon[] $objects
     * @param string $locale
     * @return TranslationTaskResult
     */
    public function bulkFetch(array $objects, string $locale): TranslationTaskResult
    {
        $result = new TranslationTaskResult();
        $bag = new TranslationBag();
        $refBag = new TranslationBag();

        try {
            foreach ($objects as $taxon) {
                $this->logger->info(sprintf('[TAXON-TRANSLATION] Fetching translations for taxon %s (#%d)', $taxon->getCode(), $taxon->getId()));
                $this->extractionTool->extractTranslations($refBag, $taxon);
            }

            $this->translatorClient->fetchAllKeys($bag, $refBag->exportPrimaryTags(), null, $this->lokalizeFechOnlyVerifiedKeys);

            if (!$bag->hasErrors()) {
                $this->validateFetchedBag($bag, $refBag, $locale);

                foreach ($objects as $taxon) {
                    $this->extractionTool->applyTranslations($bag, $taxon, $locale);
                }

                $result->setSucceeded(sizeof($bag->getKeysNames(true)));
                $this->logger->info(sprintf('[TAXON-TRANSLATION] Fetching translations succeeded (got %d keys)', sizeof($bag->getKeysNames(true))));
            }

            $result->addErrors($bag->getErrors()); // Pass bag errors to result
        } catch (TranslationException $e) {
            $result->addError($e);
            $this->logger->error(sprintf('[TAXON-TRANSLATION] Error while fetching translations: %s', $e->getMessage()));
        }

        return $result;
    }
}
