<?php

declare(strict_types=1);

namespace App\Provider\Image\Product;

use App\Entity\Product\ProductImage;
use App\Provider\Image\ImagineFilterImageProvider;
use App\Repository\Product\ProductOptionValueRepository;
use App\Repository\Product\ProductVariantRepository;

class ProductVariantImageProvider
{
    private ImagineFilterImageProvider $imagineFilterImageProvider;
    private ProductImageProvider $productImageProvider;
    private ProductVariantRepository $productVariantRepository;
    private ProductOptionValueRepository $productOptionValueRepository;

    public function __construct(
        ImagineFilterImageProvider $imagineFilterImageProvider,
        ProductImageProvider $productImageProvider,
        ProductVariantRepository $productVariantRepository,
        ProductOptionValueRepository $productOptionValueRepository
    ) {
        $this->imagineFilterImageProvider = $imagineFilterImageProvider;
        $this->productImageProvider = $productImageProvider;
        $this->productVariantRepository = $productVariantRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
    }

    public function getImagePathsByProductCodeAndColorCode(string $productCode, string $colorCode): array
    {
        $productVariant = $this->productVariantRepository->findOneByProductCodeAndOptionValue(
            $productCode,
            $colorCode
        );

        if (null === $productVariant || !$productVariant->hasImages()) {
            return $this->productImageProvider->getImagePathsByProductCode($productCode);
        }

        /** @var ProductImage $productVariantImage */
        $productVariantImage = $productVariant->getImages()->first();

        return $this->imagineFilterImageProvider->getImagePaths(
            $productVariantImage->getPath(),
            ImagineFilterImageProvider::APP_FILTER_TUNNEL_MODAL_THUMBNAIL,
            ImagineFilterImageProvider::APP_FILTER_TUNNEL_MODAL_THUMBNAIL_RETINA
        );
    }
}
