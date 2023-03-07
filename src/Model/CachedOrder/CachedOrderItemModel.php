<?php

declare(strict_types=1);

namespace App\Model\CachedOrder;

class CachedOrderItemModel
{
    protected string $variantCode = '';

    protected int $quantity = 0;

    protected ?string $comment = null;

    public function getVariantCode(): string
    {
        return $this->variantCode;
    }

    public function setVariantCode(string $variantCode): void
    {
        $this->variantCode = $variantCode;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
