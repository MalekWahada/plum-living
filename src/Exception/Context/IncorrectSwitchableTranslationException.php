<?php

declare(strict_types=1);

namespace App\Exception\Context;

class IncorrectSwitchableTranslationException extends \RuntimeException
{
    private ?string $incorrectLocale;
    private string $shouldBeLocale;

    public function __construct(?string $incorrectCode, string $shouldBeCode, ?string $message = null, \Exception $previousException = null)
    {
        $this->incorrectLocale = $incorrectCode;
        $this->shouldBeLocale = $shouldBeCode;

        parent::__construct($message ?: sprintf('Locale "%s" is not valid. Should be "%s"!', $incorrectCode, $shouldBeCode), 0, $previousException);
    }

    public function getIncorrectLocale(): ?string
    {
        return $this->incorrectLocale;
    }

    public function getShouldBeLocale(): string
    {
        return $this->shouldBeLocale;
    }
}
