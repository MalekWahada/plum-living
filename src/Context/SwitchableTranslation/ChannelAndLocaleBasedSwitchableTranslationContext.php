<?php

declare(strict_types=1);

namespace App\Context\SwitchableTranslation;

use App\Exception\Translation\SwitchableTranslationNotFoundException;
use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

class ChannelAndLocaleBasedSwitchableTranslationContext implements SwitchableTranslationContextInterface
{
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private SwitchableTranslationProvider $translationProvider;

    public function __construct(ChannelContextInterface $channelContext, LocaleContextInterface $localeContext, SwitchableTranslationProvider $translationProvider)
    {
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->translationProvider = $translationProvider;
    }

    public function getSlug(): string
    {
        try {
            $channel = $this->channelContext->getChannel();
            $locale = $this->localeContext->getLocaleCode();

            if (null !== $slug = $this->translationProvider->findSlugFromChannelAndLocale($channel->getCode(), $locale)) {
                return $slug;
            }
        } catch (ChannelNotFoundException $e) {
            throw new SwitchableTranslationNotFoundException('No channel found.');
        }

        throw new SwitchableTranslationNotFoundException('Translation slug is not found in current channel and locale context.');
    }

    public function setCustomerContext(?CustomerInterface $customer): void
    {
    }
}
