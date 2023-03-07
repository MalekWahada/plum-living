<?php

declare(strict_types=1);

namespace App\Entity\ProductIkea;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_ikea_channel_pricing")
 * @UniqueEntity(
 *     fields={"channel_code", "product_ikea_id"},
 *     groups={"sylius", "Default"}
 * )
 */
class ProductIkeaChannelPricing implements ResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $price = null;

    /**
     * @ORM\Column(type="string", nullable=false, name="channel_code")
     */
    protected string $channelCode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProductIkea\ProductIkea", inversedBy="channelPricings")
     * @ORM\JoinColumn(nullable=false, name="product_ikea_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?ProductIkea $productIkea = null;

    public function __toString(): string
    {
        return (string) $this->getPrice();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    public function setChannelCode(string $channelCode): void
    {
        $this->channelCode = $channelCode;
    }

    public function getProductIkea(): ?ProductIkea
    {
        return $this->productIkea;
    }

    public function setProductIkea(?ProductIkea $productIkea): void
    {
        $this->productIkea = $productIkea;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price = null): void
    {
        $this->price = $price;
    }
}
