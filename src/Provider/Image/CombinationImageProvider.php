<?php

declare(strict_types=1);

namespace App\Provider\Image;

use App\Entity\Taxonomy\Taxon;
use App\Provider\Tunnel\Shopping\CombinationProvider;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

class CombinationImageProvider
{
    private CombinationProvider $combinationProvider;
    private ImagineFilterImageProvider $imagineFilterImageProvider;

    public function __construct(
        CombinationProvider $combinationProvider,
        ImagineFilterImageProvider $imagineFilterImageProvider
    ) {
        $this->combinationProvider = $combinationProvider;
        $this->imagineFilterImageProvider = $imagineFilterImageProvider;
    }

    public function getCombinationImagePaths(
        ?Taxon $facadeType,
        ?ProductOptionValueInterface $design = null,
        ?ProductOptionValueInterface $finish = null,
        ?ProductOptionValueInterface $color = null
    ): array {
        $combination = $this->combinationProvider->findCombination($facadeType, $design, $finish, $color);
        if ($combination !== null && $combination->getImage() !== null) {
            return $combination->getImage()->getPath() !== null ?
                $this->imagineFilterImageProvider->getImagePaths(
                    $combination->getImage()->getPath(),
                    ImagineFilterImageProvider::APP_FILTER_PS_FINAL_STEP_PRODUCT_THUMBNAIL,
                    ImagineFilterImageProvider::APP_FILTER_PS_FINAL_STEP_PRODUCT_THUMBNAIL_RETINA
                ) : [];
        }
        return [];
    }
}
