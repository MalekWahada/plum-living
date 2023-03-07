<?php

declare(strict_types=1);

namespace App\Context;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CachedPerRequestChannelContext implements ChannelContextInterface
{
    private ChannelContextInterface $decoratedChannelContext;

    private RequestStack $requestStack;

    private SessionInterface $session;

    public function __construct(
        ChannelContextInterface $decoratedChannelContext,
        RequestStack $requestStack,
        SessionInterface $session
    ) {
        $this->decoratedChannelContext = $decoratedChannelContext;
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    public function getChannel(): ChannelInterface
    {
        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            $sessionKey = 'channel_null';
        } else {
            $objectIdentifier = spl_object_hash($request);
            $sessionKey = 'channel_' . $objectIdentifier;
        }
        $channel = $this->session->get($sessionKey, null);
        if (null === $channel) {
            $channel = $this->decoratedChannelContext->getChannel();
            $this->session->set($sessionKey, $channel);
        }

        return $channel;
    }
}
