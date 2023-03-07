<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Order\Order;
use Doctrine\Persistence\ObjectManager;
use Sylius\Bundle\UserBundle\Event\UserEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class CartBlamerListener
{
    private ObjectManager $cartManager;
    private CartContextInterface $customerCartContext;
    private CartContextInterface $sessionCartContext;

    public function __construct(
        ObjectManager        $cartManager,
        CartContextInterface $customerCartContext,
        CartContextInterface $sessionCartContext
    ) {
        $this->cartManager = $cartManager;
        $this->customerCartContext = $customerCartContext;
        $this->sessionCartContext = $sessionCartContext;
    }

    public function onImplicitLogin(UserEvent $userEvent): void
    {
        $user = $userEvent->getUser();
        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $this->blame($user);
    }

    public function onInteractiveLogin(InteractiveLoginEvent $interactiveLoginEvent): void
    {
        $user = $interactiveLoginEvent->getAuthenticationToken()->getUser();
        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $this->blame($user);
    }

    private function blame(ShopUserInterface $user): void
    {
        try {
            $sessionCart = $this->sessionCartContext->getCart();
        } catch (CartNotFoundException $e) {
            $sessionCart = null;
        }

        if (!$sessionCart instanceof Order) {
            return;
        }

        $cart = $this->getCart();

        if (null === $cart) {
            $sessionCart->setCustomer($user->getCustomer());
            $this->cartManager->persist($sessionCart);
            $this->cartManager->flush();
            return;
        }

        if ($sessionCart->getId() === $cart->getId()) {
            return;
        }

        $sessionCart->setCustomer($user->getCustomer());
        $this->cartManager->persist($sessionCart);
        $this->cartManager->remove($cart);
        $this->cartManager->flush();
    }

    private function getCart(): ?OrderInterface
    {
        try {
            $cart = $this->customerCartContext->getCart();
        } catch (CartNotFoundException $exception) {
            return null;
        }

        if (!$cart instanceof OrderInterface) {
            throw new UnexpectedTypeException($cart, OrderInterface::class);
        }

        return $cart;
    }
}
