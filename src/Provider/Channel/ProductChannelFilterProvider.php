<?php

declare(strict_types=1);

namespace App\Provider\Channel;

use App\Entity\Channel\Channel;
use App\Model\ProductChannelFilter\ProductChannelFilterModel;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductChannelFilterProvider
{
    private array $channels;

    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->translator = $translator;
        $this->channels = $channelRepository->findAll();
    }

    /**
     * build filter choices which is composed of objects having ProductChannelFilterModel type.
     *
     * @return array|ProductChannelFilterModel[]
     */
    public function getChannels(): array
    {
        $productChannels = $this->initProductChannels();
        /** @var Channel $channel */
        foreach ($this->channels as $channel) {
            $productChannel = new ProductChannelFilterModel();
            $productChannel->setCode($channel->getCode());
            $productChannel->setValue($channel->getName());

            $productChannels[] = $productChannel;
        }
        return $productChannels;
    }

    /**
     * used to initiate default channels for the filter ('all_channels' and 'no_channel');
     *
     * @return array|ProductChannelFilterModel[]
     */
    private function initProductChannels(): array
    {
        $productChannels = [];
        $defaultChannels = [
            ProductChannelFilterModel::ALL_CHANNELS,
            ProductChannelFilterModel::NO_CHANNEL,
        ];

        foreach ($defaultChannels as $channelCode) {
            $productChannel = new ProductChannelFilterModel();
            $productChannel->setCode($channelCode);
            $productChannel->setValue($this->translator->trans('app.ui.channel.' . $channelCode));

            $productChannels[] = $productChannel;
        }

        return $productChannels;
    }
}
