<?php

declare(strict_types=1);

namespace App\Model\Translation;

class TranslationKey
{
    public const PRIMARY_TAG_KEY = 'code';

    private string $name;
    private ?int $id;
    private ?string $translatableCode;
    private array $tags = [];

    /**
     * @var array|TranslationItem[]
     */
    private array $translations = [];

    public function __construct(string $name, ?int $id, ?string $translatableCode)
    {
        $this->name = $name;
        $this->id = $id;
        $this->translatableCode = $translatableCode;

        if (null !== $translatableCode) {
            $this->tags[self::PRIMARY_TAG_KEY] = $translatableCode;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTranslatableCode(): ?string
    {
        return $this->translatableCode;
    }

    public function hasTag(string $key): bool
    {
        return array_key_exists($key, $this->tags);
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTag(string $key): ?string
    {
        return $this->tags[$key] ?? null;
    }

    public function addTag(string $key, string $value): void
    {
        $this->tags[$key] = $value;
    }

    public function setTranslation(string $locale, ?string $translation): void
    {
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = new TranslationItem($locale, $translation);
        }

        $this->translations[$locale]->setValue($translation);
    }

    public function getTranslation(string $locale): ?TranslationItem
    {
        return $this->translations[$locale] ?? null;
    }

    /**
     * @param bool $removeSkippedOrEmpty
     * @return array|TranslationItem[]
     */
    public function getTranslations(bool $removeSkippedOrEmpty = false): array
    {
        if ($removeSkippedOrEmpty) {
            return array_filter($this->translations, static function (TranslationItem $translation) {
                return !$translation->isSkipped() && !$translation->isEmpty();
            });
        }

        return $this->translations;
    }

    public function isTranslationSkipped(string $locale): bool
    {
        return isset($this->translations[$locale]) && $this->translations[$locale]->isSkipped();
    }

    public function isTranslationEmpty(string $locale): bool
    {
        return isset($this->translations[$locale]) && $this->translations[$locale]->isEmpty();
    }

    public function isTranslationSkippedOrEmpty(string $locale): bool
    {
        return $this->isTranslationSkipped($locale) && $this->isTranslationEmpty($locale);
    }

    public function hasTranslationsToUpload(): bool
    {
        return !empty($this->getTranslations(true));
    }

    public function hasLocale(string $locale): bool
    {
        return isset($this->translations[$locale]);
    }

    /**
     * @return array|string[]
     */
    public function getLocales(): array
    {
        return array_keys($this->translations);
    }

    public function getValue(string $locale): ?string
    {
        return $this->hasLocale($locale) ? $this->translations[$locale]->getValue() : null;
    }

    public function hasEmptyLocale(string $locale): bool
    {
        return !isset($this->translations[$locale]) || $this->translations[$locale]->isEmpty();
    }
}
