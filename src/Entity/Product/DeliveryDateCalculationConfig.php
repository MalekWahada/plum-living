<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="plum_delivery_date_calculation_config")
 */
class DeliveryDateCalculationConfig implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=32, nullable=false, unique=true)
     */
    private ?string $mode = null;

    /**
     * @ORM\Column(name="min_date_delivery", type="date", nullable=true)
     */
    private ?\DateTime $minDateDelivery = null;

    /**
     * @ORM\Column(name="max_date_delivery", type="date", nullable=true)
     */
    private ?\DateTime $maxDateDelivery = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): void
    {
        if (null === $mode) {
            $mode = ProductVariant::DELIVERY_DATE_CALCULATION_MODE_DYNAMIC; // Default mode
        }

        $this->mode = $mode;
    }

    public function getMinDateDelivery(): ?\DateTime
    {
        return $this->minDateDelivery;
    }

    public function setMinDateDelivery(?\DateTime $minDateDelivery): void
    {
        $this->minDateDelivery = $minDateDelivery;
    }

    public function getMaxDateDelivery(): ?\DateTime
    {
        return $this->maxDateDelivery;
    }

    public function setMaxDateDelivery(?\DateTime $maxDateDelivery): void
    {
        $this->maxDateDelivery = $maxDateDelivery;
    }
}
