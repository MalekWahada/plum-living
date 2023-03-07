<?php

declare(strict_types=1);

namespace App\Converter;

class WeightUnitConverter
{
    public function convertFromGram(float $weightInGrams): float
    {
        return $weightInGrams / 1000;
    }

    public function convertFromLb(float $weightInLbs): float
    {
        return $weightInLbs * 0.45359237;
    }

    public function convertFromOz(float $weightInOz): float
    {
        return $weightInOz * 0.0283495231;
    }

    public function convertFromKg(float $weightInKg): float
    {
        return $weightInKg;
    }
}
