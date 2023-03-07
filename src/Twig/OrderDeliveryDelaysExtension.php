<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Order\Order;
use App\Provider\Order\DeliveryDelaysProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrderDeliveryDelaysExtension extends AbstractExtension
{
    private DeliveryDelaysProvider $delaysProvider;

    public function __construct(DeliveryDelaysProvider $delaysProvider)
    {
        $this->delaysProvider = $delaysProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_delivery_delays_days', [$this, 'getMinMaxDeliveryDelaysDays']),
            new TwigFunction('get_delivery_delays_dates', [$this, 'getMinMaxDeliveryDelaysDates']),
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getMinMaxDeliveryDelaysDays(Order $order): array
    {
        return $this->delaysProvider->getMinMaxDeliveryDelaysDays($order);
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getMinMaxDeliveryDelaysDates(Order$order): array
    {
        return $this->delaysProvider->getMinMaxDeliveryDelaysDates($order);
    }
}
