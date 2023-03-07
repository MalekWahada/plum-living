<?php

declare(strict_types=1);

namespace App\Provider\Image\Product;

use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use App\Provider\Image\ImagineFilterImageProvider;
use App\Repository\Product\ProductRepository;
use Sylius\Component\Core\Model\ImageInterface;
use function PHPUnit\Framework\isInstanceOf;

class ProductImageProvider
{
    private ImagineFilterImageProvider $imagineFilterImageProvider;
    private ProductRepository $productRepository;

    public function __construct(
        ImagineFilterImageProvider $imagineFilterImageProvider,
        ProductRepository $productRepository
    ) {
        $this->imagineFilterImageProvider = $imagineFilterImageProvider;
        $this->productRepository = $productRepository;
    }

    public function getImagePathsByProductCode(string $productCode): array
    {
        /** @var Product|null $product */
        $product = $this->productRepository->findOneBy(['code' => $productCode]);

        if ($product === null || !$product->hasImages()) {
            return [];
        }

        /** @var ProductImage|false $productMainImage */
        $productMainImage = $product->getImagesByType('main')->first();
        if (false !== $productMainImage) {
            return $this->getImages($productMainImage->getPath());
        }
        
        $firstImage = $product->getImages()->first();
        \assert($firstImage instanceof ImageInterface);

        return $this->getImages($firstImage->getPath());
    }

    private function getImages(string $path): array
    {
        return $this->imagineFilterImageProvider->getImagePaths(
            $path,
            ImagineFilterImageProvider::APP_FILTER_TUNNEL_MODAL_THUMBNAIL,
            ImagineFilterImageProvider::APP_FILTER_TUNNEL_MODAL_THUMBNAIL_RETINA
        );
    }
}
