<?php

declare(strict_types=1);

namespace App\Model\Translation;

class TranslationItem
{
    private string $locale;
    private ?string $value;
    private bool $isSkipped = false;

    public function __construct(string $locale, ?string $value)
    {
        $this->locale = $locale;
        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setValue(?string $value): void
    {
        if ($this->value !== $value) {
            $this->value = $value;
            $this->isSkipped = false;
        }
    }

    public function isSkipped(): bool
    {
        return $this->isSkipped;
    }

    public function evaluateSkipped(?string $originalValue): void
    {
        $this->isSkipped = $this->value === $originalValue;
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }
}
