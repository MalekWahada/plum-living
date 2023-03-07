<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Image;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sylius_product_option_value_image")
 */
class ProductOptionValueImage extends Image
{
    public const PRODUCT_OPTION_VALUE_IMAGE_TYPE_DEFAULT = 'default';
    public const PRODUCT_OPTION_VALUE_IMAGE_TYPE_DISPLAY = 'display';
    public const PRODUCT_OPTION_VALUE_IMAGE_TYPE_STYLER = 'styler';

    public const ALLOWED_PRODUCT_OPTION_VALUE_IMAGE_TYPES = [
        self::PRODUCT_OPTION_VALUE_IMAGE_TYPE_DEFAULT,
        self::PRODUCT_OPTION_VALUE_IMAGE_TYPE_DISPLAY,
        self::PRODUCT_OPTION_VALUE_IMAGE_TYPE_STYLER,
    ];

    /**
    *  @var ProductOptionValue
    *
    * @ORM\ManyToOne(targetEntity=ProductOptionValue::class, inversedBy="images")
    * @ORM\JoinColumn(nullable=false, name="product_option_value_id", referencedColumnName="id")
    */
    protected $owner;

    protected $type = self::PRODUCT_OPTION_VALUE_IMAGE_TYPE_DEFAULT;

    public function setType(?string $type): void
    {
        if (in_array($type, self::ALLOWED_PRODUCT_OPTION_VALUE_IMAGE_TYPES, true)) {
            $this->type = $type;
        } else {
            $this->type = self::PRODUCT_OPTION_VALUE_IMAGE_TYPE_DEFAULT;
        }
    }
}
