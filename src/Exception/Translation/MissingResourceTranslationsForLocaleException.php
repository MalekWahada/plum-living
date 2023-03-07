<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use Symfony\Component\Intl\Locales;
use Throwable;

class MissingResourceTranslationsForLocaleException extends TranslationException
{
    /**
     * @var array|string[]
     */
    private array $missingKeys;

    public function __construct(string $locale, array $missingKeys = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct('app.translation.missing_page_translation_for_locale_key', $code, $previous);

        $this->missingKeys = $missingKeys;
        $this->locale = $locale;

        $detailsMsg = '';
        foreach ($missingKeys as $missingKey) {
            $detailsMsg .= '%s | ';
        }
        $this->details = vsprintf($detailsMsg, $this->missingKeys);
    }

    /**
     * @return array|string[]
     */
    public function getMissingKeys(): array
    {
        return $this->missingKeys;
    }
}
