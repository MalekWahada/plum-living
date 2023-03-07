<?php

declare(strict_types=1);

namespace App\Model\ProductChannelFilter;

class ProductChannelFilterModel
{
    public const ALL_CHANNELS = 'all_channels';
    public const NO_CHANNEL = 'no_channel';

    protected ?string $code;

    protected ?string $value;

    public function __construct(string $code = '', string $value = '')
    {
        $this->code = $code;
        $this->value = $value;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
