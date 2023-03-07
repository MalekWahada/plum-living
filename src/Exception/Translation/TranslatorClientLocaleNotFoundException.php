<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use Symfony\Component\Intl\Locales;
use Throwable;

class TranslatorClientLocaleNotFoundException extends TranslatorClientException
{
    public function __construct(string $locale, int $code = 0, Throwable $previous = null)
    {
        parent::__construct('app.translation.translator_client_locale_not_found', $code, $previous);
        $this->locale = $locale;
    }
}
