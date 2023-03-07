<?php

namespace App\Controller;

use App\Entity\User\ShopUser;
use Sylius\Component\Customer\Model\CustomerInterface;

trait CustomerAwareTrait
{
    private function getCustomer(): ?CustomerInterface
    {
        /** @var ShopUser|null $shopUser */
        $shopUser = $this->getUser();
        if (null !== $shopUser) {
            return $shopUser->getCustomer();
        }
        return null;
    }
}
