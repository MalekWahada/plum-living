<?php

declare(strict_types=1);

namespace App\ApiPlatform\Message\Order;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;
use Symfony\Component\Serializer\Annotation\Groups;

final class UpdateErpRegistered implements OrderTokenValueAwareInterface
{
    public ?string $orderTokenValue;

    /**
     * @Groups({"order:update_erp_registered"})
     */
    public ?int $erpRegistered;

    public function __construct(?int $erpRegistered)
    {
        $this->erpRegistered = $erpRegistered;
    }

    public function getOrderTokenValue(): ?string
    {
        return $this->orderTokenValue;
    }

    public function setOrderTokenValue(?string $orderTokenValue): void
    {
        $this->orderTokenValue = $orderTokenValue;
    }
}
