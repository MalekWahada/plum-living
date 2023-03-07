<?php

declare(strict_types=1);

namespace App\Dto\ProductVariant;

class ProductVariantDto
{
    public function __construct(string $code = '', string $quantity = '')
    {
        $this->code = $code;
        $this->quantity = $quantity;
    }

    protected string $code;

    protected string $quantity;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): void
    {
        $this->quantity = $quantity;
    }
}
