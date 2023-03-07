<?php

declare(strict_types=1);

namespace App\Factory\Order;

use App\Entity\Order\OrderItem;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Repository\Product\ProductVariantRepository;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Assert\Assert;

final class OrderItemFactory implements CartItemFactoryInterface
{
    private CartItemFactoryInterface $decoratedFactory;
    private RequestStack $requestStack;
    private ProductVariantRepository $productVariantRepository;

    public function __construct(
        CartItemFactoryInterface $decoratedFactory,
        RequestStack $requestStack,
        ProductVariantRepository $productVariantRepository
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->requestStack = $requestStack;
        $this->productVariantRepository = $productVariantRepository;
    }

    public function createForProduct(ProductInterface $product): OrderItem
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->decoratedFactory->createForProduct($product);

        return $orderItem;
    }

    public function createForProductVariant(ProductVariantInterface $variant): OrderItem
    {
        Assert::isInstanceOf($variant, ProductVariant::class);

        $orderItem = $this->createNew();
        $orderItem->setVariant($variant);

        return $orderItem;
    }

    public function createForProductInTunnel(ProductInterface $product, array $optionValuesMatched): OrderItem
    {
        $orderItem = $this->createForProduct($product);

        // Make sure the optionValues keys exist

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $productId = $product->getId();
            $variant = $this->productVariantRepository->findVariantByOptionValues(
                $productId,
                $optionValuesMatched["facadeTypeCode"],
                $optionValuesMatched["designCode"],
                $optionValuesMatched["finishCode"],
                $optionValuesMatched["colorCode"]
            );
            $orderItem->setVariant($variant);
        }

        return $orderItem;
    }

    public function createForCart(OrderInterface $order): OrderItem
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->decoratedFactory->createForCart($order);

        return $orderItem;
    }

    public function createNew(): OrderItem
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->decoratedFactory->createNew();

        return $orderItem;
    }
}
