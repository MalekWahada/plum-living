<?php

declare(strict_types=1);

namespace App\Translation;

use App\Entity\Page\Page;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBagInterface;
use MonsieurBiz\SyliusCmsPagePlugin\Entity\PageTranslationInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;

class PageTranslationExtractionTool extends RichEditorExtractionTool
{
    private const PAGE_TRANSLATABLE_DATA_FIELDS = [
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'slug',
        'caption'
    ];

    private SlugGeneratorInterface $slugGenerator;

    public function __construct(SlugGeneratorInterface $slugGenerator, LoggerInterface $translationLogger)
    {
        parent::__construct(self::CMS_PAGE_TYPE, $translationLogger);
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * @throws TranslationException
     */
    public function applyTranslations(TranslationBagInterface $bag, Page $page, string $locale): void
    {
        $page->setFallbackLocale($page->getReferenceLocaleCode()); // Set fallback locale. In case of missing translation, it will be created automatically.
        $referenceTranslation = $page->getTranslation($page->getReferenceLocaleCode());

        $page->setFallbackLocale($locale);
        $pageTranslation = $page->getTranslation($locale);
        $code = $page->getCode();

        try {
            $keys = [];
            $translations = $bag->getPrimaryTagLocaleKeys($code, $locale);
            foreach ($translations as $translation) {
                $keys[$translation->getName()] = $translation->getValue($locale);
            }
            $keys = $this->removeCodeFromTranslatableKeys($keys, $code);

            // Static
            $this->applyStaticTranslations($pageTranslation, $keys);

            // Content
            if (null === $referenceTranslation->getContent()) {
                $this->logger->info(sprintf('Reference translation content for page (%s) is null.', $code));
                return;
            }
            $contentBlocks = json_decode($referenceTranslation->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $pageTranslation->setContent($referenceTranslation->getContent()); // Always reuse the reference content

            $this->applyContentTranslations($contentBlocks, $keys, self::RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS);
            $pageTranslation->setContent(json_encode($contentBlocks, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            $message = sprintf('Error while decoding/encoding %s (%s) content for locale %s: %s', strtoupper($this->type), $code, $locale, $e->getMessage());
            $this->logger->error($message);
            throw new TranslationException($message);
        }
    }

    /**
     * @throws TranslationException
     */
    public function extractReferenceLocaleTranslations(TranslationBagInterface $bag, Page $page): void
    {
        $locale = $page->getReferenceLocaleCode();
        $translation = $page->getTranslation($locale);
        $code = $page->getCode();
        $translations = $this->addPrefixCodeToKeys($this->extractStaticTranslations($translation), $code);

        try {
            $contentBlocks = json_decode($translation->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $result = $this->addPrefixCodeToKeys($this->extractContentTranslations($contentBlocks, self::RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS), $code);

            $translations = array_merge($translations, $result);
        } catch (\JsonException $e) {
            $message = sprintf('Error while decoding %s (%s) content for locale %s: %s', strtoupper($this->type), $code, $locale, $e->getMessage());
            $this->logger->error($message);
            throw new TranslationException($message);
        }

        foreach ($translations as $keyName => $transText) {
            $key = $bag->add($keyName, $transText, $locale, null, $code);

            if (null !== $page->getType()) {
                $key->addTag('type', $page->getType());
            }

            if (null !== $page->getCategory()) {
                $key->addTag('category', $page->getCategory());
            }
        }
    }

    private function extractStaticTranslations(PageTranslationInterface $pageTrans): array
    {
        return [
            'slug' => $pageTrans->getSlug(),
            'title' => $pageTrans->getTitle(),
            'meta_title' => $pageTrans->getMetaTitle(),
            'meta_description' => $pageTrans->getMetaDescription(),
            'meta_keywords' => $pageTrans->getMetaKeywords(),
        ];
    }

    private function applyStaticTranslations(PageTranslationInterface $pageTrans, array $translationsKeys): void
    {
        $slug = null;
        $mediaArticlePrefix = Page::PAGE_TYPE_MEDIA_ARTICLE . '/';
        if (isset($translationsKeys['slug'])) {
            if (str_starts_with($translationsKeys['slug'], $mediaArticlePrefix)) {
                $slug = $mediaArticlePrefix . $this->slugGenerator->generate(str_replace($mediaArticlePrefix, '', $translationsKeys['slug'])); // Remove media article prefix from generator
            } else {
                $slug = $this->slugGenerator->generate($translationsKeys['slug']);
            }
        }
        $pageTrans->setSlug($slug);
        $pageTrans->setTitle($translationsKeys['title'] ?? null);
        $pageTrans->setMetaTitle($translationsKeys['meta_title'] ?? null);
        $pageTrans->setMetaDescription($translationsKeys['meta_description'] ?? null);
        $pageTrans->setMetaKeywords($translationsKeys['meta_keywords'] ?? null);
    }
}
