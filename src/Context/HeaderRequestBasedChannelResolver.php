<?php

declare(strict_types=1);

namespace App\Context;

use App\Repository\Channel\ChannelRepository;
use Sylius\Component\Channel\Context\RequestBased\RequestResolverInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Component\HttpFoundation\Request;

class HeaderRequestBasedChannelResolver implements RequestResolverInterface
{
    private const HTTP_HEADER_NAME = 'CF-IPCountry';
    private ChannelRepository $channelRepository;

    public function __construct(ChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function findChannel(Request $request): ?ChannelInterface
    {
        $headerCountryCode = $request->headers->get(self::HTTP_HEADER_NAME);

        if (empty($headerCountryCode) || strlen($headerCountryCode) !== 2) {
            return null;
        }

        return $this->channelRepository->findOneByCountry($headerCountryCode); // Case-insensitive
    }
}
