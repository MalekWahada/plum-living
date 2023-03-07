<?php

declare(strict_types=1);

namespace App\Provider\CMS\Button;

class ButtonProvider
{
    public const BUTTON_TYPE_DEFAULT = 'default';
    public const BUTTON_TYPE_SIMPLE_LINK = 'simple_link';
    public const BUTTON_TYPE_CLASSIC = 'classic';

    public const ALLOWED_BUTTON_TYPES = [
        self::BUTTON_TYPE_DEFAULT,
        self::BUTTON_TYPE_SIMPLE_LINK,
        self::BUTTON_TYPE_CLASSIC,
    ];

    public function getButtonTypes(): array
    {
        return self::ALLOWED_BUTTON_TYPES;
    }
}
