<?php

declare(strict_types=1);

namespace App\Provider\CMS\ImagesSteps;

class ImagesStepsProvider
{
    public const IMAGES_STEPS_TYPE_DEFAULT = 'default';
    public const IMAGES_STEPS_TYPE_INDEXED = 'indexed';

    public const ALLOWED_IMAGES_STEPS_TYPES = [
        self::IMAGES_STEPS_TYPE_DEFAULT,
        self::IMAGES_STEPS_TYPE_INDEXED,
    ];

    public function getImagesStepsTypes(): array
    {
        return self::ALLOWED_IMAGES_STEPS_TYPES;
    }
}
