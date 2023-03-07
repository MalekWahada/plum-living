<?php

declare(strict_types=1);

namespace App\Provider\Order;

use App\Entity\Order\Order;
use App\Erp\Providers\NetSuiteOrderStatusProvider;
use Sylius\Component\Core\OrderPaymentStates;

class StatusProvider
{
    public const STATE_CANCELLED = 'state_cancelled';
    public const STATE_FULFILLED = 'state_fulfilled';
    public const STATE_FULLY_PAID = 'state_fully_paid';
    public const STATE_FULLY_SHIPPED = 'state_fully_shipped';
    public const STATE_IN_CART = 'state_in_cart';
    public const STATE_PARTIALLY_PAID = 'state_partially_paid';
    public const STATE_PARTIALLY_SHIPPED = 'state_partially_shipped';
    public const STATE_PENDING = 'state_pending';

    private NetSuiteOrderStatusProvider $suiteOrderStatusProvider;

    public function __construct(NetSuiteOrderStatusProvider $suiteOrderStatusProvider)
    {
        $this->suiteOrderStatusProvider = $suiteOrderStatusProvider;
    }

    /**
     * Don't forget to update Grid filter "OrderState" if you change the states
     * @param Order $order
     * @return string
     */
    public function getAccountStatusLabel(Order $order): string
    {
        $erpStatus = $order->getErpStatus();

        if ($erpStatus === Order::DEFAULT_ERP_STATUS) {
            $orderState = $order->getState();
            if ($orderState === Order::STATE_CART) {
                return self::STATE_IN_CART;
            }

            $paymentState = $order->getPaymentState();
            if ($paymentState === OrderPaymentStates::STATE_PAID) {
                return self::STATE_FULLY_PAID;
            }
            if ($paymentState === OrderPaymentStates::STATE_PARTIALLY_PAID) {
                return self::STATE_PARTIALLY_PAID;
            }
        } elseif ($erpStatus !== null) {
            $sluggedStatus = $this->suiteOrderStatusProvider->getSluggedStatus($erpStatus);
            switch ($sluggedStatus) {
                case NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_BILLED:
                    return self::STATE_FULFILLED;
                case NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_DELIVERY_IN_PROGRESS:
                case NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_DELIVERED:
                    return self::STATE_FULLY_SHIPPED;
                case NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_PRODUCTION_IN_PROGRESS:
                    return self::STATE_PARTIALLY_SHIPPED;
                case NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_CANCELED:
                    return self::STATE_CANCELLED;
                default:
                    return self::STATE_PENDING;
            }
        }

        return self::STATE_PENDING;
    }
}
