<?php

declare(strict_types=1);

namespace App\Product\Image;

use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use Sylius\Component\Core\Model\ImageInterface;

class DefaultMainImageAssigner
{
    public function setMainTypeImage(Product $product): Product
    {
        $images = $product->getImages();
        if (!$images->isEmpty()) {
            $hasMainImage = false;

            if (!$product->getImagesByType(ProductImage::MAIN_TYPE)->isEmpty()) {
                $hasMainImage = true;
            }

            if (!$hasMainImage) {
                $firstImage = $images->first();
                \assert($firstImage instanceof ImageInterface);
                $firstImage->setType(ProductImage::MAIN_TYPE);
            }
        }

        return $product;
    }
}
