<?php

declare(strict_types=1);

namespace App\Checkout\Shipping;

use App\Entity\Order\Order;
use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionAction;
use App\Entity\Shipping\Shipment;
use App\Entity\Shipping\ShippingMethod;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Shipping\Calculator\DelegatingCalculatorInterface as ShippingCalculator;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;

final class ShippingPriceCalculator
{
    private ShippingCalculator $calculator;
    private CalculatorInterface $taxCalculator;
    private TaxRateResolverInterface $taxRateResolver;
    private PromotionActionCommandInterface $actionCommand;

    public function __construct(
        ShippingCalculator              $calculator,
        CalculatorInterface             $taxCalculator,
        PromotionActionCommandInterface $actionCommand,
        TaxRateResolverInterface        $taxRateResolver
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->calculator = $calculator;
        $this->taxRateResolver = $taxRateResolver;
        $this->actionCommand = $actionCommand;
    }

    public function calculatePrice(ShippingMethod $shippingMethod, Order $order): float
    {
        $orderAdjustments = $order->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT);
        /** @var Shipment $shipment */
        $shipment = $order->getShipments()->last();
        $shipment->setMethod($shippingMethod);

        $shippingPromotions = $order->getAdjustments(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT);
        $coupon = $order->getPromotionCoupon();
        $hasFreeSampleShipping = $shippingPromotions->exists(
            static function (int $key, BaseAdjustmentInterface $adjustment): bool {
                return 'Free_Shipping_Samples' === $adjustment->getOriginCode();
            }
        );

        if ($hasFreeSampleShipping || ($coupon && 0 < \count($shippingPromotions->getValues()))) {
            foreach ($orderAdjustments as $adjustment) {
                $adjustment->setAmount($this->calculator->calculate($shipment));
            }

            $order->recalculateAdjustmentsTotal();

            if ($coupon) {
                $promotion = $coupon->getPromotion();
                \assert($promotion instanceof Promotion);

                $promotionAction = $promotion->getActions()->first();
                \assert($promotionAction instanceof PromotionAction);

                $this->actionCommand->execute(
                    $order,
                    $promotionAction->getConfiguration(),
                    $coupon->getPromotion()
                );
                $order->recalculateAdjustmentsTotal();
            }

            $reduction = $order->getAdjustmentsTotal(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT);

            if (0 === ($this->calculator->calculate($shipment) + $reduction)) {
                return .0;
            }
        }

        return $this->calculator->calculate($shipment)
            + $this->taxCalculator->calculate(
                $this->calculator->calculate($shipment),
                $this->taxRateResolver->resolve($shippingMethod)
            );
    }
}
