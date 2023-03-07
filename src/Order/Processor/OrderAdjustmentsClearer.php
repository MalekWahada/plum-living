<?php

declare(strict_types=1);

namespace App\Order\Processor;

use App\EmailManager\OrderProcessing\OrderAdjustmentsTotalInconsistencyEmailManager;
use App\Entity\Order\OrderItem;
use App\Entity\Order\OrderItemUnit;
use Exception;
use Psr\Log\LoggerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use function sprintf;

class OrderAdjustmentsClearer implements OrderProcessorInterface
{
    private OrderProcessorInterface $decoratedProcessor;
    private LoggerInterface $logger;
    private OrderAdjustmentsTotalInconsistencyEmailManager $orderAdjustmentsTotalInconsistencyEmailManager;
    private RequestStack $requestStack;

    public function __construct(
        OrderProcessorInterface $decoratedProcessor,
        LoggerInterface $orderPricesLogger,
        OrderAdjustmentsTotalInconsistencyEmailManager $orderAdjustmentsTotalInconsistencyEmailManager,
        RequestStack $requestStack
    ) {
        $this->decoratedProcessor = $decoratedProcessor;
        $this->logger = $orderPricesLogger;
        $this->orderAdjustmentsTotalInconsistencyEmailManager = $orderAdjustmentsTotalInconsistencyEmailManager;
        $this->requestStack = $requestStack;
    }

    public function process(OrderInterface $order): void
    {
        // The following code is a fix for some orders that have weird adjustments in DB
        $originalTotal = $order->getTotal();
        $order->recalculateAdjustmentsTotal();
        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $item->recalculateAdjustmentsTotal();
            /** @var OrderItemUnit $unit */
            foreach ($item->getUnits() as $unit) {
                $unit->recalculateAdjustmentsTotal();
            }
        }
        $newTotal = $order->getTotal();

        // In case the recalculation is giving different amount, log an error for this order
        if ($originalTotal !== $newTotal) {
            $this->logger->error(sprintf(
                'Order "%s" (id: %d) had a recalculated total of %d, previously %d',
                $order->getNumber(),
                $order->getId(),
                $originalTotal,
                $newTotal
            ));

            try {
                $this->orderAdjustmentsTotalInconsistencyEmailManager->sendOrderAdjustmentsTotalInconsistencyEmail(
                    $order,
                    $originalTotal,
                    $newTotal
                );
            } catch (Exception $_) {
                // Just catch exception to avoid any error during this flow. As email sending can sometimes have temp errors.
            }

            if (null !== $this->requestStack->getMasterRequest()
                && $this->requestStack->getMasterRequest()->attributes->get('_route') !== "ajax_cart_update"
                && !$this->requestStack->getMasterRequest()->isXmlHttpRequest()
            ) {
                throw new Exception('Une erreur est survenue sur votre panier.');
            }
        }

        // Resume original process of clearing adjustments
        $this->decoratedProcessor->process($order);
    }
}
