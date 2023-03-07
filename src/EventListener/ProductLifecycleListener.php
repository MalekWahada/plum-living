<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

class ProductLifecycleListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Warning: on postDelete event, the entity does not have an ID anymore.
     * @param ProductInterface $product
     * @param LifecycleEventArgs $args
     */
    public function onProductDeleted(ProductInterface $product, LifecycleEventArgs $args): void
    {
        Assert::isInstanceOf($product, ProductInterface::class);

        $this->logger->alert(sprintf('[PRODUCT] Product with code %s has been deleted.', $product->getCode()), [
            'code' => $product->getCode(),
            'trace' => debug_backtrace()
        ]);
    }

    public function onProductVariantDeleted(ProductVariantInterface $productVariant, LifecycleEventArgs $args): void
    {
        Assert::isInstanceOf($productVariant, ProductVariantInterface::class);

        $this->logger->alert(sprintf('[PRODUCT-VARIANT] Product variant with code %s has been deleted.', $productVariant->getCode()), [
            'code' => $productVariant->getCode(),
            'trace' => debug_backtrace()
        ]);
    }
}
