<?php

declare(strict_types=1);

namespace App\Context;

use App\Exception\Context\RequestStackInvalidMasterRequest;
use App\Provider\Translation\RequestStackSwitchableTranslationSlugProvider;
use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Context\RequestBased\RequestResolverInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestBasedChannelResolver implements RequestResolverInterface
{
    private SwitchableTranslationProvider $translationProvider;
    private RequestStackSwitchableTranslationSlugProvider $slugProvider;

    public function __construct(SwitchableTranslationProvider $translationProvider, RequestStackSwitchableTranslationSlugProvider $slugProvider)
    {
        $this->translationProvider = $translationProvider;
        $this->slugProvider = $slugProvider;
    }

    public function findChannel(Request $request): ?ChannelInterface
    {
        try {
            if (null !== $slug = $this->slugProvider->getSlug()) {
                return $this->translationProvider->findChannelFromSlug($slug);
            }
        } catch (RequestStackInvalidMasterRequest $e) {
            throw new ChannelNotFoundException('No master request found.', $e);
        }

        return null; // Do not throw exception because HeaderRequestBasedChannelResolver will not be called.
    }
}
