<?php


namespace App\Entity\Product;

use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface as ProductOptionValueTranslationBaseInterface;

interface ProductOptionValueTranslationInterface extends ProductOptionValueTranslationBaseInterface
{
    public function getDescription(): ?string;

    public function setDescription(?string $description): void;
}
