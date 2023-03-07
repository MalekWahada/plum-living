<?php

declare(strict_types=1);

namespace App\Context\SwitchableTranslation;

use Sylius\Component\Core\Model\CustomerInterface;

interface SwitchableTranslationContextInterface
{
    public function getSlug(): string;

    public function setCustomerContext(?CustomerInterface $customer): void;
}
