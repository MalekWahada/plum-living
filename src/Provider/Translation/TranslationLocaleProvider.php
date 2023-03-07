<?php

declare(strict_types=1);

namespace App\Provider\Translation;

use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;

class TranslationLocaleProvider implements TranslationLocaleProviderInterface
{
    private TranslationLocaleProviderInterface $decoratedProvider;

    public function __construct(TranslationLocaleProviderInterface $decoratedProvider)
    {
        $this->decoratedProvider = $decoratedProvider;
    }

    public function getDefinedLocalesCodes(): array
    {
        return $this->decoratedProvider->getDefinedLocalesCodes();
    }

    public function getDefinedLocalesCodesOrDefault(): array
    {
        $codes = $this->decoratedProvider->getDefinedLocalesCodes();
        return !empty($codes) ? $codes : [$this->getDefaultLocaleCode()];
    }


    public function getDefaultLocaleCode(): string
    {
        return $this->decoratedProvider->getDefaultLocaleCode();
    }
}
