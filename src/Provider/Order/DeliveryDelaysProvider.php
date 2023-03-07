<?php

declare(strict_types=1);

namespace App\Provider\Order;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Entity\Product\DeliveryDateCalculationConfig;
use App\Entity\Product\ProductVariant;
use App\Repository\Product\DeliveryDateCalculationConfigRepository;
use DateTime;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;

class DeliveryDelaysProvider
{
    private DeliveryDateCalculationConfigRepository $calculationConfigRepository;
    private bool $calculationConfigRetrieved = false;
    private DateTime $now;

    /**
     * @var DeliveryDateCalculationConfig[]
     */
    private array $calculationConfigs = [];

    public function __construct(DeliveryDateCalculationConfigRepository $deliveryDateCalculationConfigRepository)
    {
        $this->calculationConfigRepository = $deliveryDateCalculationConfigRepository;
        $this->now = (new DateTime())->setTime(00, 00, 00); // Time must be reset for comparison
    }

    /**
     * @param Order $order
     * @return array{'min' : DateTime|null, 'max' : DateTime|null}
     */
    public function getMinMaxDeliveryDelaysDays(Order $order): array
    {
        $delays = ['min' => $order->getMinDateDelivery(), 'max' => $order->getMaxDateDelivery()];

        if (null === $order->getMinDateDelivery()) {
            $delays['min'] = new \DateTime();
            $delays['min']->modify('+' . $this->getMinDeliveryDelaysDays($order) . ' day');
        }

        if (null === $order->getMaxDateDelivery()) {
            $delays['max'] = new \DateTime();
            $delays['max']->modify('+' . $this->getMaxDeliveryDelaysDays($order) . ' day');
        }
        return $delays;
    }

    public function getMinDeliveryDelaysDays(Order $order): int
    {
        $configs = $this->getCalculationConfigs();
        $min = 0;

        /** @var OrderItem $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $minDelay = 0;

            switch ($orderItem->getVariant()->getDeliveryCalculationMode()) {
                case ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER:
                    if (isset($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER])) {
                        $minDelay = $this->getMinDayDeliveryFromConfig($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER]);
                    }
                    break;
                case ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD:
                    if (isset($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD])) {
                        $minDelay = $this->getMinDayDeliveryFromConfig($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD]);
                    }
                    break;
                default: // DELIVERY_DATE_CALCULATION_MODE_DYNAMIC
                    $minDelay = $orderItem->getVariant()->getMinDayDelivery();
                    break;
            }

            $min = ($minDelay > $min)? $minDelay : $min;
        }

        return $min;
    }

    public function getMaxDeliveryDelaysDays(Order $order): int
    {
        $configs = $this->getCalculationConfigs();
        $max = 0;

        /** @var OrderItem $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $maxDelay = 0;

            switch ($orderItem->getVariant()->getDeliveryCalculationMode()) {
                case ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER:
                    if (isset($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER])) {
                        $maxDelay = $this->getMaxDayDeliveryFromConfig($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER]);
                    }
                    break;
                case ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD:
                    if (isset($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD])) {
                        $maxDelay = $this->getMaxDayDeliveryFromConfig($configs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD]);
                    }
                    break;
                default: // DELIVERY_DATE_CALCULATION_MODE_DYNAMIC
                    $maxDelay = $orderItem->getVariant()->getMaxDayDelivery();
                    break;
            }

            $max = ($maxDelay > $max)? $maxDelay : $max;
        }

        return $max;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getMinMaxDeliveryDelaysDates(Order $order): array
    {
        $delaysDays = $this->getMinMaxDeliveryDelaysDays($order);
        if ($order->getCheckoutState() === OrderCheckoutStates::STATE_COMPLETED) {
            return $delaysDays;
        }

        return $this->recalculateMinMaxDeliveryDelaysDates($order);
    }

    /**
     * @param Order $order
     * @return array{'min' : Datetime, 'max' : Datetime}
     */
    public function recalculateMinMaxDeliveryDelaysDates(Order $order): array
    {
        $delaysDates = ['min' => new \DateTime(), 'max' => new \DateTime()];

        $delaysDate['min'] = $delaysDates['min']->modify('+' . $this->getMinDeliveryDelaysDays($order) . ' day');
        $delaysDate['max'] = $delaysDates['max']->modify('+' . $this->getMaxDeliveryDelaysDays($order) . ' day');

        return $delaysDates;
    }

    private function getCalculationConfigs(): array
    {
        if (!$this->calculationConfigRetrieved) {
            $this->calculationConfigs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER] = $this->calculationConfigRepository->findByMode(ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER);
            $this->calculationConfigs[ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD] = $this->calculationConfigRepository->findByMode(ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD);
            $this->calculationConfigRetrieved = true;
        }

        return $this->calculationConfigs;
    }

    /**
     * @return int
     */
    private function getMinDayDeliveryFromConfig(DeliveryDateCalculationConfig $config): int
    {
        if (null !== $config->getMinDateDelivery()) {
            return (int) $config->getMinDateDelivery()->diff($this->now)->days;
        }
        return 0;
    }

    /**
     * @return int
     */
    private function getMaxDayDeliveryFromConfig(DeliveryDateCalculationConfig $config): int
    {
        if (null !== $config->getMaxDateDelivery()) {
            return (int) $config->getMaxDateDelivery()->diff($this->now)->days;
        }
        return 0;
    }
}
