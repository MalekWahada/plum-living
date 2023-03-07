<?php

declare(strict_types=1);

namespace App\Context;

use App\Entity\Channel\Channel;
use App\Exception\Context\RequestStackInvalidMasterRequest;
use App\Model\Translation\SwitchableTranslation;
use App\Provider\Translation\RequestStackSwitchableTranslationSlugProvider;
use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChannelBasedLocaleContext implements LocaleContextInterface
{
    private ChannelContextInterface $channelContext;
    private SessionInterface $session;
    private SwitchableTranslationProvider $translationProvider;
    private RequestStackSwitchableTranslationSlugProvider $slugProvider;

    public function __construct(
        ChannelContextInterface $channelContext,
        SessionInterface        $session,
        SwitchableTranslationProvider $translationProvider,
        RequestStackSwitchableTranslationSlugProvider $slugProvider
    ) {
        $this->channelContext = $channelContext;
        $this->session = $session;
        $this->translationProvider = $translationProvider;
        $this->slugProvider = $slugProvider;
    }

    public function getLocaleCode(): string
    {
        try {
            /** @var Channel $channel */
            $channel = $this->channelContext->getChannel();

            if (null === $channel->getDefaultLocale()) {
                throw new LocaleNotFoundException('Default locale is not set for channel.');
            }

            try {
                if (null === $slug = $this->slugProvider->getSlug()) {
                    throw new LocaleNotFoundException('No slug found on the master request.');
                }

                $localeCode = $this->translationProvider->findLocaleCodeFromSlug($slug, $channel->getCode());
                if (null !== $localeCode) {
                    $this->session->set(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER, $slug);
                    return $localeCode;
                }
            } catch (RequestStackInvalidMasterRequest $e) {
                throw new LocaleNotFoundException('No master request available.');
            }

            throw new LocaleNotFoundException('No locale found for slug: ' . $slug); // Another provider will return channel's default locale.
        } catch (ChannelNotFoundException $exception) {
            throw new LocaleNotFoundException();
        }
    }
}
