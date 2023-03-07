<?php

declare(strict_types=1);

namespace App\Model\CMSFilter;

class ListingProjectFilterModel
{
    protected ?string $piece;

    protected ?string $color;

    public function __construct(string $piece = '', string $color = '')
    {
        $this->piece = $piece;
        $this->color = $color;
    }

    public function getPiece(): ?string
    {
        return $this->piece;
    }

    public function setPiece(?string $piece): void
    {
        $this->piece = $piece;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }
}
