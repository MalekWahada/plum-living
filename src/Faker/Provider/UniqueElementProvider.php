<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use Faker\Generator;

final class UniqueElementProvider
{
    private Generator $generator;
    private static array $usedElements = [];

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function uniqueElements(array $elements, int $count = 1): object
    {
        self::$usedElements = [];

        $unusedElements = array_udiff($elements, self::$usedElements, function ($a, $b) {
            return spl_object_hash($a) <=> spl_object_hash($b);
        });

        $chosenElements = $this->generator->randomElements($unusedElements, $count);
        self::$usedElements = array_merge(self::$usedElements, $chosenElements);

        return current($chosenElements);
    }
}
