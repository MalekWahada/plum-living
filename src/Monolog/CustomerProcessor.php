<?php

declare(strict_types=1);

namespace App\Monolog;

use App\Entity\User\ShopUser;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class CustomerProcessor
{
    private ?UserInterface $user = null;
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(array $log): array
    {
        if ($this->user instanceof ShopUser && null !== $this->user->getCustomer()) {
            $log['extra']['customer_id'] = $this->user->getCustomer()->getId();
        }

        return $log;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) { // Not authenticated
            return;
        }

        $this->user = $user;
    }
}
