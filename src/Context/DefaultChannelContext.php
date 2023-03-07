<?php

declare(strict_types=1);

namespace App\Context;

use App\Entity\Channel\Channel;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

class DefaultChannelContext implements ChannelContextInterface
{
    private ChannelRepositoryInterface $channelRepository;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function getChannel(): ChannelInterface
    {
        $channel = $this->channelRepository->findOneByCode(Channel::DEFAULT_CODE);

        if (null === $channel) {
            throw new ChannelNotFoundException();
        }

        return $channel;
    }
}
