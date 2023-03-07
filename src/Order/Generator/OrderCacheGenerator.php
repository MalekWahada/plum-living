<?php

declare(strict_types=1);

namespace App\Order\Generator;

use App\Entity\Order\Order;
use App\Model\CachedOrder\CachedOrderItemModel;
use App\Model\CachedOrder\CachedOrderModel;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

final class OrderCacheGenerator implements OrderCacheGeneratorInterface
{
    // 7 days expiration
    private const ORDER_EXPIRE_CACHE = 3600 * 24 * 7;

    private TokenGeneratorInterface $tokenGenerator;
    private FilesystemAdapter $cache;

    public function __construct(
        TokenGeneratorInterface $tokenGenerator,
        string $defaultCacheDir
    ) {
        $this->tokenGenerator = $tokenGenerator;
        $this->cache = new FilesystemAdapter('', self::ORDER_EXPIRE_CACHE, $defaultCacheDir);
    }

    public function generate(Order $order): string
    {
        $cachedOrder = new CachedOrderModel();
        $orderCacheToken = $this->tokenGenerator->generateToken();
        $cachedOrder->setCacheKey($orderCacheToken);

        foreach ($order->getItems() as $item) {
            $cachedItem = new CachedOrderItemModel();

            $cachedItem->setVariantCode($item->getVariant()->getCode());
            $cachedItem->setQuantity($item->getQuantity());
            $cachedItem->setComment($item->getComment());

            $cachedOrder->addCachedItem($cachedItem);
        }
        $this->cache->get($orderCacheToken, fn (): CachedOrderModel => $cachedOrder);

        return $orderCacheToken;
    }

    public function getCachedOrder(string $token): ?CachedOrderModel
    {
        return $this->cache->getItem($token)->get();
    }
}
