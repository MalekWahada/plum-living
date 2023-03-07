<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use Throwable;

class MissingResourceTranslationKeyException extends TranslationException
{
    private array $missingKeys;

    public function __construct(array $missingKeys = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct('app.translation.missing_page_translation_key', $code, $previous);
        $this->missingKeys = $missingKeys;

        $detailsMsg = '';
        foreach ($missingKeys as $missingKey) {
            $detailsMsg .= '%s | ';
        }
        $this->details = vsprintf($detailsMsg, $this->missingKeys);
    }

    public function getMissingKeys(): array
    {
        return $this->missingKeys;
    }
}
