<?php

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Product\ProductCompleteInfo;
use App\Entity\Product\ProductCompleteInfoTranslation;
use App\Entity\Product\ProductGroup;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBagInterface;
use Psr\Log\LoggerInterface;

class ProductCompleteInfoTranslationExtractionTool extends RichEditorExtractionTool
{
    public function __construct(LoggerInterface $translationLogger)
    {
        parent::__construct(self::PRODUCT_COMPLETE_INFO_TYPE, $translationLogger);
    }

    /**
     * @throws TranslationException
     */
    public function applyTranslations(TranslationBagInterface $bag, ProductCompleteInfo $completeInfo, string $locale): void
    {
        $completeInfo->setFallbackLocale(ProductCompleteInfoTranslation::PUBLISHED_LOCALE);
        /** @var ProductCompleteInfoTranslation $referenceTranslation */
        $referenceTranslation = $completeInfo->getTranslation(ProductCompleteInfoTranslation::PUBLISHED_LOCALE);

        $completeInfo->setFallbackLocale($locale); // Set fallback locale. In case of missing translation, it will be created automatically.
        /** @var ProductCompleteInfoTranslation $completeInfoTranslation */
        $completeInfoTranslation = $completeInfo->getTranslation($locale);

        if (null === $completeInfo->getProduct()) {
            return;
        }

        $code = $completeInfo->getProduct()->getCode();

        try {
            $keys = [];
            $translations = $bag->getPrimaryTagLocaleKeys($code, $locale);
            foreach ($translations as $translation) {
                $keys[$translation->getName()] = $translation->getValue($locale);
            }
            $keys = $this->removeCodeFromTranslatableKeys($keys, $code);

            $contentBlocks = json_decode($referenceTranslation->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $completeInfoTranslation->setContent($referenceTranslation->getContent()); // Always reuse the reference content

            $this->applyContentTranslations($contentBlocks, $keys, self::RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS);
            $completeInfoTranslation->setContent(json_encode($contentBlocks, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            $message = sprintf('Error while decoding/encoding %s (product %s) content for locale %s: %s', strtoupper($this->type), $code, $locale, $e->getMessage());
            $this->logger->error($message);
            throw new TranslationException($message);
        }
    }

    /**
     * @throws TranslationException
     */
    public function extractTranslations(TranslationBagInterface $bag, ProductCompleteInfo $completeInfo): void
    {
        $locale = ProductCompleteInfoTranslation::PUBLISHED_LOCALE;

        /** @var ProductCompleteInfoTranslation $completeInfoTranslation */
        $completeInfoTranslation = $completeInfo->getTranslation($locale);
        $translations = [];

        if (null === $completeInfo->getProduct()) {
            return;
        }

        $code = $completeInfo->getProduct()->getCode();

        try {
            $contentBlocks = json_decode($completeInfoTranslation->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $result = $this->addPrefixCodeToKeys($this->extractContentTranslations($contentBlocks, self::RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS), $code);

            $translations = array_merge($translations, $result);
        } catch (\JsonException $e) {
            $message = sprintf('Error while decoding %s (product %s) content for locale %s: %s', strtoupper($this->type), $code, $locale, $e->getMessage());
            $this->logger->error($message);
            throw new TranslationException($message);
        }

        foreach ($translations as $keyName => $transText) {
            $key = $bag->add($keyName, $transText, $locale, null, $code);

            if (null !== $completeInfo->getProduct()->getMainTaxon()) {
                $key->addTag('main_taxon', $completeInfo->getProduct()->getMainTaxon()->getCode());
            }

            if (!$completeInfo->getProduct()->getGroups()->isEmpty()) {
                $key->addTag('groups', implode(',', $completeInfo->getProduct()->getGroups()->map(function (ProductGroup $group) {
                    return $group->getCode();
                })->toArray()));
            }
        }
    }
}
