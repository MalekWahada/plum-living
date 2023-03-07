<?php

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonTranslation;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBagInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

class TaxonTranslationExtractionTool extends RichEditorExtractionTool
{
    private const TAXON_TRANSLATABLE_DATA_FIELDS = [
        'name',
        'slug',
        'description',
    ];

    private SlugGeneratorInterface $slugGenerator;

    public function __construct(SlugGeneratorInterface $slugGenerator, LoggerInterface $translationLogger)
    {
        parent::__construct(self::TAXON_TYPE, $translationLogger);
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * @throws TranslationException
     */
    public function applyTranslations(TranslationBagInterface $bag, Taxon $taxon, string $locale): void
    {
        $taxon->setFallbackLocale(TaxonTranslation::PUBLISHED_LOCALE); // Set fallback locale. In case of missing translation, it will be created automatically.
        /** @var TaxonTranslation $referenceTranslation */
        $referenceTranslation = $taxon->getTranslation(TaxonTranslation::PUBLISHED_LOCALE);

        $taxon->setFallbackLocale($locale);
        /** @var TaxonTranslation $taxonTranslation */
        $taxonTranslation = $taxon->getTranslation($locale);
        $code = $taxon->getCode();

        try {
            $keys = [];
            $translations = $bag->getPrimaryTagLocaleKeys($code, $locale);
            foreach ($translations as $translation) {
                $keys[$translation->getName()] = $translation->getValue($locale);
            }
            $keys = $this->removeCodeFromTranslatableKeys($keys, $code);

            // Static
            $this->applyStaticTranslations($taxonTranslation, $keys);

            // Content
            if (null === $referenceTranslation->getProductInfo()) {
                $this->logger->info(sprintf('Reference translation content for taxon (%s) is null.', $code));
                return;
            }
            $contentBlocks = json_decode($referenceTranslation->getProductInfo(), true, 512, JSON_THROW_ON_ERROR);

            $taxonTranslation->setProductInfo($referenceTranslation->getProductInfo()); // Always reuse the reference content

            $this->applyContentTranslations($contentBlocks, $keys, self::RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS);
            $taxonTranslation->setProductInfo(json_encode($contentBlocks, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            $message = sprintf('Error while decoding/encoding %s (%s) content for locale %s: %s', strtoupper($this->type), $code, $locale, $e->getMessage());
            $this->logger->error($message);
            throw new TranslationException($message);
        }
    }

    /**
     * @throws TranslationException
     */
    public function extractTranslations(TranslationBagInterface $bag, Taxon $taxon): void
    {
        /** @var TaxonTranslation $translation */
        $translation = $taxon->getTranslation(TaxonTranslation::PUBLISHED_LOCALE);
        $code = $taxon->getCode();
        $translations = $this->addPrefixCodeToKeys($this->extractStaticTranslations($translation), $code);

        try {
            $contentBlocks = json_decode($translation->getProductInfo(), true, 512, JSON_THROW_ON_ERROR);
            $result = $this->addPrefixCodeToKeys($this->extractContentTranslations($contentBlocks, self::RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS), $code);

            $translations = array_merge($translations, $result);
        } catch (\JsonException $e) {
            $message = sprintf('Error while decoding %s (%s) content for locale %s: %s', strtoupper($this->type), $code, TaxonTranslation::PUBLISHED_LOCALE, $e->getMessage());
            $this->logger->error($message);
            throw new TranslationException($message);
        }

        foreach ($translations as $keyName => $transText) {
            $bag->add($keyName, $transText, TaxonTranslation::PUBLISHED_LOCALE, null, $code);
        }
    }

    private function extractStaticTranslations(TaxonTranslationInterface $taxonTranslation): array
    {
        return [
            'name' => $taxonTranslation->getName(),
            'slug' => $taxonTranslation->getSlug(),
            'description' => $taxonTranslation->getDescription(),
        ];
    }

    private function applyStaticTranslations(TaxonTranslationInterface $taxonTranslation, array $translationsKeys): void
    {
        $taxonTranslation->setName($translationsKeys['name'] ?? null);
        $taxonTranslation->setSlug(isset($translationsKeys['slug']) ? $this->slugGenerator->generate($translationsKeys['slug']) : null);
        $taxonTranslation->setDescription($translationsKeys['description'] ?? null);
    }
}
