<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Order\Order;
use App\Entity\Taxonomy\Taxon;
use App\TagManager\GoogleTagManagerEvent;
use App\TagManager\TagManagerEventInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class AfterPaymentListener
{
    private GoogleTagManagerEvent $tagManagerEvent;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        GoogleTagManagerEvent $tagManagerEvent,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->tagManagerEvent = $tagManagerEvent;
        $this->orderRepository = $orderRepository;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!$event->isMasterRequest()) {
            return;
        }

        if (!is_array($controller)) {
            return;
        }

        if ($controller[1] !== 'thankYouAction') {
            return;
        }

        $orderId = $event->getRequest()->getSession()->get('sylius_order_id');
        if ($orderId === null) {
            return;
        }

        /** @var Order|null $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order) {
            return;
        }

        $this->tagManagerEvent->push(
            $order->hasItemType(Taxon::TAXON_SAMPLE_CODE)
                ? TagManagerEventInterface::ORDER_PAID_WITH_SAMPLE_EVENT
                : TagManagerEventInterface::ORDER_PAID_WITHOUT_SAMPLE_EVENT
        );

        if ($order->hasItemType(Taxon::TAXON_SAMPLE_FRONT_CODE)) {
            $this->tagManagerEvent->push(TagManagerEventInterface::ORDER_PAID_WITH_FRONT_SAMPLE_EVENT);
        }

        if ($order->hasItemType(Taxon::TAXON_SAMPLE_PAINT_CODE)) {
            $this->tagManagerEvent->push(TagManagerEventInterface::ORDER_PAID_WITH_PAINT_SAMPLE_EVENT);
        }

        $this->tagManagerEvent->setData('DataLayerTransactionRevenue', ($order->getTotal() - $order->getShippingTotal()) / 1000);
    }
}
