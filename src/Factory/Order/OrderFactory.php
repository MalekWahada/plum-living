<?php

declare(strict_types=1);

namespace App\Factory\Order;

use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Entity\CustomerProject\Project;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class OrderFactory implements FactoryInterface
{
    private FactoryInterface $decoratedFactory;

    private OrderTokenAssignerInterface $orderTokenAssigner;

    private OrderItemFactory $orderItemFactory;

    private OrderItemQuantityModifierInterface $orderItemQuantityModifier;

    private OrderProcessorInterface $orderProcessor;

    private CartContextInterface $cartContext;

    public function __construct(
        FactoryInterface $decoratedFactory,
        OrderTokenAssignerInterface $orderTokenAssigner,
        OrderItemFactory $orderItemFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        OrderProcessorInterface $orderProcessor,
        CartContextInterface $cartContext
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->orderTokenAssigner = $orderTokenAssigner;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderProcessor = $orderProcessor;
        $this->cartContext = $cartContext;
    }

    public function createNew(): OrderInterface
    {
        /** @var OrderInterface $order */
        $order = $this->decoratedFactory->createNew();

        // set a token value for all new order (not only when set from cart to new)
        $this->orderTokenAssigner->assignTokenValue($order);

        return $order;
    }

    public function createForProject(
        Project $project,
        ChannelInterface $channel,
        string $localeCode,
        string $currencyCode,
        Customer $customer = null
    ): OrderInterface {
        /** @var Order $order */
        $order = $this->cartContext->getCart();

        if (null !== $customer) {
            $order->setCustomer($customer);
        }

        $order->setChannel($channel);
        $order->setLocaleCode($localeCode);
        $order->setCurrencyCode($currencyCode);

        foreach ($project->getItems() as $item) {
            $variant = $item->getChosenVariant();

            if (null !== $variant && null !== $productVariant = $variant->getProductVariant()) {
                $orderItem = $this->orderItemFactory->createForProductVariant($productVariant);

                // adjust quantity
                $this->orderItemQuantityModifier->modify($orderItem, $variant->getQuantity());

                // set comment
                $orderItem->setComment($item->getComment());

                $order->addItem($orderItem);
            }
        }

        // processing order
        $this->orderProcessor->process($order);

        return $order;
    }
}
