<?php

declare(strict_types=1);

namespace App\Model\CachedOrder;

class CachedOrderModel
{
    protected ?string $cacheKey = null;

    /** @var array|CachedOrderItemModel[]  */
    protected array $cachedOrderItems = [];

    public function getCacheKey(): ?string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(?string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    public function getCachedOrderItems(): array
    {
        return $this->cachedOrderItems;
    }

    public function addCachedItem(CachedOrderItemModel $itemModel): void
    {
        $this->cachedOrderItems[] = $itemModel;
    }
}
