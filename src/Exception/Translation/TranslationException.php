<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use Exception;

class TranslationException extends Exception
{
    protected ?string $details = null;
    protected ?string $locale = null;

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): void
    {
        $this->details = $details;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }
}
