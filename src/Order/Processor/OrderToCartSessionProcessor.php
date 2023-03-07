<?php

declare(strict_types=1);

namespace App\Order\Processor;

use App\Entity\Order\Order;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Storage\CartStorageInterface;

class OrderToCartSessionProcessor
{
    private ChannelContextInterface $channelContext;

    private CartStorageInterface $cartStorage;
    public function __construct(ChannelContextInterface $channelContext, CartStorageInterface $cartStorage)
    {
        $this->channelContext = $channelContext;
        $this->cartStorage = $cartStorage;
    }

    // The cart session will not be pointed to the reverted order, unless we re-login.
    // so we have to make a callback cart session updater
    public function process(Order $order): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        $this->cartStorage->setForChannel($channel, $order);
    }
}
