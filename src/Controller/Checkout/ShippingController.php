<?php

declare(strict_types=1);

namespace App\Controller\Checkout;

use App\Checkout\Shipping\ShippingPriceCalculator;
use App\Entity\Order\Order;
use App\Entity\Shipping\ShippingMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ShippingController
{
    private ShippingPriceCalculator $shippingPriceCalculator;

    public function __construct(
        ShippingPriceCalculator $shippingPriceCalculator
    ) {
        $this->shippingPriceCalculator = $shippingPriceCalculator;
    }
    /**
     * @Entity("shippingMethod", expr="repository.findOneBy({'code': method})")
     * @Entity("order", expr="repository.find(order)")
     */
    public function ajaxGetShippingMethodPrice(
        ShippingMethod $shippingMethod,
        Order $order
    ): JsonResponse {
        return new JsonResponse(
            $this->shippingPriceCalculator->calculatePrice($shippingMethod, $order)
        );
    }
}
