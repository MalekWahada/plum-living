<?php

declare(strict_types=1);

namespace App\Model\Translation;

class TranslationBag extends TranslationErrorContainer implements TranslationBagInterface
{
    /** @var array|TranslationKey[]  */
    private array $keys = [];

    public function add(string $key, ?string $text, string $locale, ?int $keyId, ?string $translatableCode): TranslationKey
    {
        if (!isset($this->keys[$key])) {
            $this->keys[$key] = new TranslationKey($key, $keyId, $translatableCode);
        }

        $this->keys[$key]->setTranslation($locale, $text);
        return $this->keys[$key];
    }

    public function addKey(TranslationKey $key): TranslationKey
    {
        $this->keys[$key->getName()] = $key;
        return $key;
    }

    public function getKey(string $key): ?TranslationKey
    {
        return $this->keys[$key] ?? null;
    }

    /**
     * @return TranslationKey[]|array
     */
    public function getLocaleKeys(string $locale, bool $removeSkippedOrEmpty = false): array
    {
        return array_filter($this->keys, static function (TranslationKey $key) use ($removeSkippedOrEmpty, $locale) {
            return $key->hasLocale($locale) && (!$removeSkippedOrEmpty || !$key->isTranslationSkippedOrEmpty($locale));
        });
    }

    /**
     * @return TranslationKey[]|array
     */
    public function getPrimaryTagLocaleKeys(string $primaryTag, string $locale, bool $removeSkippedOrEmpty = false): array
    {
        return array_filter($this->keys, static function (TranslationKey $key) use ($primaryTag, $removeSkippedOrEmpty, $locale) {
            return $key->hasLocale($locale)
                && $key->hasTag(TranslationKey::PRIMARY_TAG_KEY)
                && $primaryTag === $key->getTag(TranslationKey::PRIMARY_TAG_KEY)
                && (!$removeSkippedOrEmpty || !$key->isTranslationSkippedOrEmpty($locale));
        });
    }


    /**
     * @return TranslationKey[]|array
     */
    public function getAll(bool $removeSkippedOrEmpty = false): array
    {
        if ($removeSkippedOrEmpty) {
            return array_filter($this->keys, static function (TranslationKey $key) {
                return $key->hasTranslationsToUpload();
            });
        }
        return $this->keys;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        if ($locale === null) {
            return array_key_exists($key, $this->keys);
        }

        return isset($this->keys[$key]) && $this->keys[$key]->hasLocale($locale);
    }

    /**
     * @return array|string[]
     */
    public function getKeysNames(bool $removeSkippedOrEmpty = false): array
    {
        return array_keys($this->getAll($removeSkippedOrEmpty));
    }

    /**
     * @return array|string[]
     */
    public function getLocaleKeysNames(string $locale, bool $removeSkippedOrEmpty = false): array
    {
        return array_keys($this->getLocaleKeys($locale, $removeSkippedOrEmpty));
    }

    /**
     * @return array|TranslationKey[]
     */
    public function getEmptyKeysForLocale(string $locale): array
    {
        $emptyKeys = [];
        foreach ($this->keys as $key) {
            if (!$key->hasLocale($locale) || $key->hasEmptyLocale($locale)) {
                $emptyKeys[] = $key;
            }
        }
        return $emptyKeys;
    }

    /**
     * @param string|null $filterKey
     * @param bool $prefixWithKey
     * @return array|string[]
     */
    public function exportTags(?string $filterKey = null, bool $prefixWithKey = true): array
    {
        $tags = [];
        foreach ($this->keys as $key) {
            if ($filterKey !== null && $key->getName() !== $filterKey) {
                continue;
            }
            foreach ($key->getTags() as $tagKey => $tag) {
                if ($prefixWithKey) {
                    $tag = sprintf('%s:%s', $tagKey, $tag);
                }
                if (!in_array($tag, $tags, true)) {
                    $tags[] = $tag;
                }
            }
        }
        return $tags;
    }

    /**
     * @param string|null $filterKey
     * @param bool $prefixWithKey
     * @return array|string[]
     */
    public function exportPrimaryTags(?string $filterKey = null, bool $prefixWithKey = true): array
    {
        $tags = [];
        foreach ($this->keys as $key) {
            if ($filterKey !== null && $key->getName() !== $filterKey) {
                continue;
            }
            $tag = $key->getTag(TranslationKey::PRIMARY_TAG_KEY);
            if ($prefixWithKey) {
                $tag = sprintf('%s:%s', TranslationKey::PRIMARY_TAG_KEY, $tag);
            }
            if (null !== $tag && !in_array($tag, $tags, true)) {
                $tags[] = $tag;
            }
        }
        return $tags;
    }

    public function getLocales(): array
    {
        $locales = [];
        foreach ($this->keys as $key) {
            foreach ($key->getLocales() as $locale) {
                if (!in_array($locale, $locales, true)) {
                    $locales[] = $locale;
                }
            }
        }
        return $locales;
    }

    public function hasLocale(string $locale): bool
    {
        return count(array_filter($this->keys, static function (TranslationKey $key) use ($locale) {
            return $key->hasLocale($locale);
        })) > 0;
    }

    public function isEmpty(): bool
    {
        return count($this->keys) === 0;
    }
}
