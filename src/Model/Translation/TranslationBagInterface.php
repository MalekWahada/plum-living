<?php

declare(strict_types=1);

namespace App\Model\Translation;

interface TranslationBagInterface extends TranslationErrorContainerInterface
{
    public function add(string $key, ?string $text, string $locale, ?int $keyId, ?string $translatableCode): TranslationKey;

    public function addKey(TranslationKey $key): TranslationKey;

    public function getKey(string $key): ?TranslationKey;

    /**
     * @return TranslationKey[]|array
     */
    public function getLocaleKeys(string $locale, bool $removeSkippedOrEmpty = false): array;

    /**
     * @return TranslationKey[]|array
     */
    public function getPrimaryTagLocaleKeys(string $primaryTag, string $locale, bool $removeSkippedOrEmpty = false): array;

    /**
     * @return TranslationKey[]|array
     */
    public function getAll(bool $removeSkippedOrEmpty = false): array;

    public function has(string $key, ?string $locale = null): bool;

    /**
     * @return array|string[]
     */
    public function getKeysNames(bool $removeSkippedOrEmpty = false): array;

    /**
     * @return array|string[]
     */
    public function getLocaleKeysNames(string $locale, bool $removeSkippedOrEmpty = false): array;

    /**
     * @param string $locale
     * @return array|TranslationKey[]
     */
    public function getEmptyKeysForLocale(string $locale): array;

    /**
     * @return array|string[]
     */
    public function exportTags(?string $filterKey = null): array;

    /**
     * @return array|string[]
     */
    public function exportPrimaryTags(?string $filterKey = null): array;

    /**
     * @return array|string[]
     */
    public function getLocales(): array;

    public function hasLocale(string $locale): bool;

    public function isEmpty(): bool;
}
