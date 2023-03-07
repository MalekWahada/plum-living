<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shop_user")
 */
class ShopUser extends BaseShopUser
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $stripeId = null;

    /**
     * @return ?string
     */
    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    /**
     * @param ?string $stripeId
     */
    public function setStripeId(?string $stripeId): void
    {
        $this->stripeId = $stripeId;
    }
}
