<?php

declare(strict_types=1);

namespace App\Provider\ImportExport;

use Sylius\Component\Resource\Repository\RepositoryInterface;

class LocalizedFieldsProvider
{
    private const FIELD_SUFFIXED_WITH_LOCALE_REGEX = '#^([a-z]+)_(([A-Za-z]{2})(?>_([A-Za-z]{2}))?)$#ii'; // Capture "Name_fr_BE" format
    private RepositoryInterface $localeRepository;

    public function __construct(RepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * Merge input  with $appendedFields suffixed with locale codes.
     * @param array $input
     * @param array $fields
     * @return array
     */
    public function mergeLocalizedFields(array $input, array $fields): array
    {
        foreach ($fields as $field) {
            foreach ($this->generateLocalizedFieldName($field) as $locale) {
                $input[] = $locale;
            }
        }
        return $input;
    }

    public function generateLocalizedFieldName(string $fieldName): array
    {
        $locales = $this->localeRepository->findAll();
        $localeFields = [];
        foreach ($locales as $locale) {
            $localeCode = $locale->getCode();
            $localeFields[$localeCode] = ucfirst($fieldName) . '_' . $localeCode;
        }

        return $localeFields;
    }

    /**
     * Extract locale suffixed field names list to multidimensional array.
     * @param array $input
     * @return array
     */
    public function extractLocalizedFields(array $input): array
    {
        $fields = [];
        $fieldsWithLocales = preg_grep(self::FIELD_SUFFIXED_WITH_LOCALE_REGEX, $input);
        $availableLocaleCodes = array_map(static function ($locale) {
            return $locale->getCode();
        }, $this->localeRepository->findAll());

        foreach ($fieldsWithLocales as $fieldWithLocale) {
            preg_match(self::FIELD_SUFFIXED_WITH_LOCALE_REGEX, $fieldWithLocale, $matches);
            $field = $matches[1] ?? null;
            $locale = $matches[2] ?? null;

            if (isset($field, $locale) && in_array($locale, $availableLocaleCodes, true)) { // Locale must be available
                if (!isset($fields[$field])) {
                    $fields[$field] = [];
                }
                $fields[$field][$locale] = $fieldWithLocale;
            }
        }

        return $fields;
    }
}
