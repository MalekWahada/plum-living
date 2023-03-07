<?php

declare(strict_types=1);

namespace App\Transformer\MonsieurBiz;

final class TextTransformer
{
    private const MONSIEURBIZ_TEXT = 'monsieurbiz.text';

    public function transform(?string $elements): ?string
    {
        if (!is_string($elements)) {
            return null;
        }

        $elements = json_decode($elements, true, 512, JSON_THROW_ON_ERROR);

        if (empty($elements)|| json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $element = $elements[0];

        if (!isset($element['data'], $element['data']['content'])) {
            return null;
        }

        return strip_tags($element['data']['content']);
    }

    public function reverseTransform(?string $content): ?string
    {
        if (empty($content)) {
            return null;
        }

        return json_encode([[
            'code' => self::MONSIEURBIZ_TEXT,
            'data' => [
                'content' => sprintf('<p>%s</p>', $content),
                'alignment' => ''
            ]
        ]], JSON_THROW_ON_ERROR);
    }
}
