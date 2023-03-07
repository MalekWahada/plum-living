<?php

declare(strict_types=1);

namespace App\Context\SwitchableTranslation;

use App\Entity\Customer\Customer;
use App\Exception\Translation\SwitchableTranslationNotFoundException;
use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;

/**
 * Find the slug from preferred channel and locale
 */
class CustomerBasedSwitchableTranslationContext implements SwitchableTranslationContextInterface
{
    private SwitchableTranslationProvider $translationProvider;
    private ?Customer $customer = null;

    public function __construct(SwitchableTranslationProvider $translationProvider)
    {
        $this->translationProvider = $translationProvider;
    }

    public function getSlug(): string
    {
        if ($this->customer === null) {
            throw new SwitchableTranslationNotFoundException('Customer is not set.');
        }

        if (null === $this->customer->getChannelCode() || null === $this->customer->getLocaleCode()) {
            throw new SwitchableTranslationNotFoundException('Channel or locale is not set.');
        }

        if (null === $slug = $this->translationProvider->findSlugFromChannelAndLocale($this->customer->getChannelCode(), $this->customer->getLocaleCode())) {
            throw new SwitchableTranslationNotFoundException('Translation slug is not found in current channel and locale context.');
        }

        return $slug;
    }

    public function setCustomerContext(?CustomerInterface $customer): void
    {
        /** @var Customer $customer */
        Assert::isInstanceOf($customer, Customer::class);

        $this->customer = $customer;
    }
}
