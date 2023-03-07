<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductTranslation as BaseProductTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_translation")
 */
class ProductTranslation extends BaseProductTranslation
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $deliveryDescription = null;

    public function getDeliveryDescription(): ?string
    {
        return $this->deliveryDescription;
    }

    public function setDeliveryDescription(?string $deliveryDescription): void
    {
        $this->deliveryDescription = $deliveryDescription;
    }
}
