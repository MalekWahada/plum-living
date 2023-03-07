<?php

declare(strict_types=1);

namespace App\Order\Processor;

use App\Entity\Order\Order;
use DateTime;

final class RevertToCartDatesProcessor
{
    public function process(Order $order): void
    {
        $order->setCreatedAt(new DateTime());
    }
}
