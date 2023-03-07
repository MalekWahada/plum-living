<?php

declare(strict_types=1);

namespace App\Formatter\CMS;

class TextToArrayFormatter
{
    public static function format(string $text): ?array
    {
        $text = str_replace(["\r\n", "\n", "\r"], '', $text);

        return json_decode($text, true);
    }
}
