<?php

declare(strict_types=1);

namespace App\Entity\ProductIkea;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Image;

/**
 * @ORM\Entity()
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="path",
 *          column=@ORM\Column(
 *              name     = "path",
 *              type     = "string",
 *              nullable = true
 *          )
 *      )
 * })
 * @ORM\Table(name="product_ikea_image")
 */
class ProductIkeaImage extends Image
{
    /**
     * @var ProductIkea
     * @ORM\OneToOne(targetEntity="App\Entity\ProductIkea\ProductIkea", inversedBy="image")
     * @ORM\JoinColumn(nullable=false, name="product_ikea_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;
}
