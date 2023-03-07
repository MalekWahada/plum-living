<?php

declare(strict_types=1);

namespace App\Model\CMSFilter;

class ListingInspirationFilterModel
{
    protected ?string $chip;

    public function __construct(string $chip = '')
    {
        $this->chip = $chip;
    }

    public function getChip(): ?string
    {
        return $this->chip;
    }

    public function setChip(?string $chip): void
    {
        $this->chip = $chip;
    }
}
