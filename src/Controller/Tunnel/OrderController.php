<?php

declare(strict_types=1);

namespace App\Controller\Tunnel;

use App\Formatter\Order\OrderToArrayFormatter;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends AbstractController
{
    private OrderRepository $orderRepository;

    private OrderItemRepository $orderItemRepository;

    private OrderItemQuantityModifierInterface $orderItemQuantityModifier;

    private OrderToArrayFormatter $orderToArrayFormatter;

    private OrderProcessorInterface $orderProcessor;

    public function __construct(
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        OrderToArrayFormatter $orderToArrayFormatter,
        OrderProcessorInterface $orderProcessor
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderToArrayFormatter = $orderToArrayFormatter;
        $this->orderProcessor = $orderProcessor;
    }

    public function ajaxUpdate(Request $request) : JsonResponse
    {
        $itemsForm = $request->request->get('sylius_cart');

        $order = $this->orderRepository->find($request->request->get('cart_id'));

        if (!$order || $order->getState() !== OrderInterface::STATE_CART) {
            return new JsonResponse('failed');
        }

        if (count($itemsForm['items']) > 0) {
            foreach ($itemsForm['items'] as $id => $item) {
                $orderItem = $this->orderItemRepository->find($id);
                if (null !== $orderItem) {
                    $quantity = (int)$item['quantity'];
                    if (0 === $quantity) {
                        $order->removeItem($orderItem);
                        $this->orderItemRepository->remove($orderItem);
                    } else {
                        $this->orderItemQuantityModifier->modify($orderItem, $quantity);
                        $order->addItem($orderItem);
                    }

                    $this->orderProcessor->process($order);
                }
            }
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse($this->orderToArrayFormatter->format($order));
        }

        return new JsonResponse('failed');
    }
}
