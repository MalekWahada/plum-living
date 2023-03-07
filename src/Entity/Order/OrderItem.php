<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order_item")
 *
 * @method ProductVariant|null getVariant()
 * @method Product|null getProduct()
 */
class OrderItem extends BaseOrderItem
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $comment = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * SubTotal does not include any tax
     * @return int
     */
    public function getSubTotalWithoutAdjustments(): int
    {
        return $this->getUnitPrice() * $this->getQuantity();
    }
}
