<?php

declare(strict_types=1);

namespace App\Provider\User;

use App\Entity\User\ShopUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ShopUserProvider
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getShopUser(): ?ShopUser
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof ShopUser) {
            return null;
        }

        return $user;
    }
}
