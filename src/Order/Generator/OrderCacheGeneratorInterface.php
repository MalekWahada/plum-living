<?php

declare(strict_types=1);

namespace App\Order\Generator;

use App\Entity\Order\Order;
use App\Model\CachedOrder\CachedOrderModel;

interface OrderCacheGeneratorInterface
{
    /**
     * used to generate cached items from an existing order items.
     *
     * @param Order $order
     * @return string
     */
    public function generate(Order $order): string;

    /**
     * given a token, retrieve the cached order from cache if it exists.
     *
     * @param string $token
     * @return CachedOrderModel|null
     */
    public function getCachedOrder(string $token): ?CachedOrderModel;
}
