<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress
{
    /**
     * @ORM\Column(name="shipping_notes", type="string", length=150, nullable=true)
     */
    private ?string $shippingNotes = null;

    public function getShippingNotes(): ?string
    {
        return $this->shippingNotes;
    }

    public function setShippingNotes(?string $shippingNotes): void
    {
        $this->shippingNotes = $shippingNotes;
    }
}
