<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Order\Order;
use App\Provider\Order\StatusProvider;
use Sylius\Component\Order\Context\CartContextInterface;
use Twig\Extension\RuntimeExtensionInterface;

class OrderRuntime implements RuntimeExtensionInterface
{
    private StatusProvider $orderStatusProvider;
    private CartContextInterface $cartContext;

    public function __construct(
        StatusProvider $orderStatusProvider,
        CartContextInterface $cartContext
    ) {
        $this->orderStatusProvider = $orderStatusProvider;
        $this->cartContext = $cartContext;
    }

    public function getAccountStatusLabel(Order $order): string
    {
        return $this->orderStatusProvider->getAccountStatusLabel($order);
    }

    public function getCurrentCartId(): ?int
    {
        return $this->cartContext->getCart()->getId();
    }
}
