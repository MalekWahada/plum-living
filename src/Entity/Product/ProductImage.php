<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductImage as BaseProductImage;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_image")
 */
class ProductImage extends BaseProductImage
{
    public const MAIN_TYPE = 'main';

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private ?int $position = 0;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }
}
