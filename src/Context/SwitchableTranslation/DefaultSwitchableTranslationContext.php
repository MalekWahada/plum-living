<?php

declare(strict_types=1);

namespace App\Context\SwitchableTranslation;

use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Core\Model\CustomerInterface;

class DefaultSwitchableTranslationContext implements SwitchableTranslationContextInterface
{
    private SwitchableTranslationProvider $translationProvider;

    public function __construct(SwitchableTranslationProvider $translationProvider)
    {
        $this->translationProvider = $translationProvider;
    }

    public function getSlug(): string
    {
        return $this->translationProvider->getDefaultSlug();
    }

    public function setCustomerContext(?CustomerInterface $customer): void
    {
    }
}
