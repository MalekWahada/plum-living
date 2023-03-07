<?php

declare(strict_types=1);

namespace App\Provider\Image;

class ColorProvider
{
    public function getImageColor(string $filePath): ?string
    {
        $fileContent = file_get_contents($filePath);
        \assert(is_string($fileContent));
        
        $image = imagecreatefromstring($fileContent);
        if ($image) {
            $index = imagecolorat($image, 10, 10);
            \assert(is_int($index));
            $rgb = imagecolorsforindex($image, $index);
            \assert(is_array($rgb));

            return sprintf("#%02x%02x%02x", $rgb['red'], $rgb['green'], $rgb['blue']);
        }

        return null;
    }

    /**
     * calculate the luminosity contrast of a given Hex value.
     * @see https://stackoverflow.com/a/42921358/10659090
     * @param string $hexColor
     * @return string
     */
    public function getContrastColor(string $hexColor): string
    {
        // hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

        // Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
            0.7152 * pow($G1 / 255, 2.2) +
            0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
            0.7152 * pow($G2BlackColor / 255, 2.2) +
            0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else {
            // if not, return white color.
            return '#FFFFFF';
        }
    }

    public function isWhite(string $string): bool
    {
        $whiteCodes = ['blanc', 'white'];
        foreach ($whiteCodes as $whiteCode) {
            if (strpos($string, $whiteCode)) {
                return true;
            }
        }
        return false;
    }
}
