<?php

declare(strict_types=1);

namespace App\Calculator\CMS;

class LectureTimeCalculator
{
    /** @link https://fr.wikipedia.org/wiki/Mot_par_minute#Lecture */
    private const WORD_READ_PER_MINUTES = 250;

    public function calculateLectureTime(array $content): ?int
    {
        $texts = array_filter($content, fn (array $obj): bool => $obj['code'] === 'monsieurbiz.text');
        $count = 0;
        foreach ($texts as $text) {
            $count += str_word_count($text['data']['content']);
        }

        return (int) ceil($count / self::WORD_READ_PER_MINUTES);
    }
}
