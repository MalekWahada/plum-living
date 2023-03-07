<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValueTranslation as BaseProductOptionValueTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_option_value_translation")
 */
class ProductOptionValueTranslation extends BaseProductOptionValueTranslation implements ProductOptionValueTranslationInterface
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
