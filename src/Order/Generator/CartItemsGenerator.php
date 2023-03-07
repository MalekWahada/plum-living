<?php

declare(strict_types=1);

namespace App\Order\Generator;

use App\Entity\Order\Order;
use App\Entity\Product\ProductVariant;
use App\Factory\Order\OrderItemFactory;
use App\Model\CachedOrder\CachedOrderModel;
use App\Model\ProductVariant\ProductVariantModel;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Order\Repository\OrderItemRepositoryInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;

class CartItemsGenerator
{
    private OrderItemFactory $orderItemFactory;
    private OrderItemQuantityModifierInterface $orderItemQuantityModifier;
    private OrderProcessorInterface $orderProcessor;
    private OrderRepositoryInterface $orderRepository;
    private OrderItemRepositoryInterface $orderItemRepository;
    private CartContextInterface $cartContext;
    private ProductVariantRepositoryInterface $productVariantRepository;

    public function __construct(
        OrderItemFactory $orderItemFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        OrderProcessorInterface $orderProcessor,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        CartContextInterface $cartContext,
        ProductVariantRepositoryInterface $productVariantRepository
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderProcessor = $orderProcessor;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->cartContext = $cartContext;
        $this->productVariantRepository = $productVariantRepository;
    }


    /**
     * In order to not pump the database with duplicated "state" orders,
     * we use a cache model instead.
     *
     * @param CachedOrderModel $orderToDuplicate
     */
    public function generateViaCachedOrder(CachedOrderModel $orderToDuplicate): void
    {
        /** @var Order $currentOrder */
        $currentOrder = $this->cartContext->getCart();

        foreach ($orderToDuplicate->getCachedOrderItems() as $cachedItem) {
            $variant = $this->getVariant($cachedItem->getVariantCode());

            if (null === $variant) {
                continue;
            }

            $existentOrderItem = $this->getOrderItem($currentOrder, $variant);

            if (null === $existentOrderItem) {
                $newOrderItem = $this->orderItemFactory->createForCart($currentOrder);
                $newOrderItem->setVariant($variant);
                $newOrderItem->setComment($cachedItem->getComment());

                $this->orderItemQuantityModifier->modify($newOrderItem, $cachedItem->getQuantity());
                $currentOrder->addItem($newOrderItem);
            } else {
                $targetQuantity = $existentOrderItem->getQuantity() + $cachedItem->getQuantity();
                $this->orderItemQuantityModifier->modify($existentOrderItem, $targetQuantity);
            }
        }
        $this->orderProcessor->process($currentOrder);
        $this->orderRepository->add($currentOrder);
    }

    /**
     * create order items for the current cart via a given product variant model through a URL.
     *
     * @param ProductVariantModel[]|array $productVariantModels
     */
    public function generateViaProductVariantModels(array $productVariantModels): void
    {
        /** @var Order $currentCart */
        $currentCart = $this->cartContext->getCart();

        /** @var ProductVariantModel $productVariantModel */
        foreach ($productVariantModels as $productVariantModel) {
            $variant = $this->getVariant($productVariantModel->getCode());

            if (null === $variant) {
                continue;
            }

            $existentOrderItem = $this->getOrderItem($currentCart, $variant);

            if (null === $existentOrderItem) {
                $newOrderItem = $this->orderItemFactory->createForCart($currentCart);
                $newOrderItem->setVariant($variant);

                $this->orderItemQuantityModifier->modify($newOrderItem, $productVariantModel->getQuantity());
                $currentCart->addItem($newOrderItem);
            } else {
                $targetQuantity = $existentOrderItem->getQuantity() + $productVariantModel->getQuantity();
                $this->orderItemQuantityModifier->modify($existentOrderItem, $targetQuantity);
            }
        }
        $this->orderProcessor->process($currentCart);
        $this->orderRepository->add($currentCart);
    }

    private function getVariant(string $variantCode): ?ProductVariant
    {
        return $this->productVariantRepository->findOneBy([
            'code' => $variantCode
        ]);
    }

    private function getOrderItem(Order $order, ProductVariant $variant): ?OrderItemInterface
    {
        return $this->orderItemRepository->findOneBy([
            'order' => $order,
            'variant' => $variant,
        ]);
    }
}
