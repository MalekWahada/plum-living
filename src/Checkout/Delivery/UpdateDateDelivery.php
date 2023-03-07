<?php

declare(strict_types=1);

namespace App\Checkout\Delivery;

use App\Entity\Order\Order;
use App\Provider\Order\DeliveryDelaysProvider;

class UpdateDateDelivery
{
    private DeliveryDelaysProvider $delaysProvider;

    public function __construct(DeliveryDelaysProvider $delaysProvider)
    {
        $this->delaysProvider = $delaysProvider;
    }

    public function updateMinMaxDelays(Order $order): void
    {
        $delays = $this->delaysProvider->getMinMaxDeliveryDelaysDates($order);

        $order->setMinDateDelivery($delays['min']);
        $order->setMaxDateDelivery($delays['max']);
    }

    public function recalculateAndUpdateMinMaxDelays(Order $order): void
    {
        $delays = $this->delaysProvider->recalculateMinMaxDeliveryDelaysDates($order);

        $order->setMinDateDelivery($delays['min']);
        $order->setMaxDateDelivery($delays['max']);
    }
}
