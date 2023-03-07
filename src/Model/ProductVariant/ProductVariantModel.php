<?php

declare(strict_types=1);

namespace App\Model\ProductVariant;

class ProductVariantModel
{
    public function __construct(string $code = '', int $quantity = 0)
    {
        $this->code = $code;
        $this->quantity = $quantity;
    }

    protected string $code;

    protected int $quantity;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
