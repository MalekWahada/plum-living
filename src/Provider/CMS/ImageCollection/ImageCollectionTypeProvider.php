<?php

declare(strict_types=1);

namespace App\Provider\CMS\ImageCollection;

class ImageCollectionTypeProvider
{
    public const IMAGE_COLLECTION_TYPE_DEFAULT = 'default';
    public const IMAGE_COLLECTION_TYPE_MOSAIC = 'mosaic';
    public const IMAGE_COLLECTION_TYPE_SLIDER = 'slider';
    public const IMAGE_COLLECTION_TYPE_SLIDER_PLANS = 'slider_plans';
    public const IMAGE_COLLECTION_TYPE_SLIDER_RIGHT = 'slider_right';

    public const ALLOWED_IMAGE_COLLECTION_TYPES = [
        self::IMAGE_COLLECTION_TYPE_DEFAULT,
        self::IMAGE_COLLECTION_TYPE_MOSAIC,
        self::IMAGE_COLLECTION_TYPE_SLIDER,
        self::IMAGE_COLLECTION_TYPE_SLIDER_PLANS,
        self::IMAGE_COLLECTION_TYPE_SLIDER_RIGHT,
    ];

    public function getImageCollectionTypeChoices(): array
    {
        return self::ALLOWED_IMAGE_COLLECTION_TYPES;
    }
}
