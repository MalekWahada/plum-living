<?php

declare(strict_types=1);

namespace App\Order\Resolver;

use App\Entity\Order\Order;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderPaymentTransitions;

final class OrderPaymentStateTransitionResolver
{
    public function getTargetTransition(OrderInterface $orderObject): ?string
    {
        /** @var Order $order */
        $order = $orderObject;

        return
            $this->getRefundTransition($order) ??
            $this->getPayTransition($order) ??
            $this->getAuthorizeTransition($order) ??
            $this->getRequestPaymentTransition($order);
    }

    private function getRefundTransition(Order $order): ?string
    {
        $refundedPayments = $order->getPaymentsByState(PaymentInterface::STATE_REFUNDED);
        $refundedPaymentTotal = $this->getPaymentsAmountsByState($refundedPayments);

        if (0 < $refundedPayments->count() && $refundedPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_REFUND;
        }

        if ($refundedPaymentTotal < $order->getTotal() && 0 < $refundedPaymentTotal) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_REFUND;
        }

        return null;
    }

    // order can be considered as paid/partially_pay in 4 cases:
    // - the completedPaymentTotal is GTE the orderTotal and there is at least a payment ---> pay
    // - the order is for free in other words the order payments list is empty ---> pay
    // - the order is a reverted free cart (all payments are cancelled) ---> pay
    //
    // - the completedPaymentTotal is LT the orderTotal  ---> partially_pay
    // else we return no transition
    private function getPayTransition(Order $order): ?string
    {
        $completedPayments = $order->getPaymentsByState(PaymentInterface::STATE_COMPLETED);
        $completedPaymentTotal = $this->getPaymentsAmountsByState($completedPayments);

        // a "free reverted cart" has only cancelled payment(s) on checkout
        $cancelledOrderPayments = $order->getPaymentsByState(PaymentInterface::STATE_CANCELLED);

        if ((0 < $completedPayments->count() && $completedPaymentTotal >= $order->getTotal()) ||
            $order->getPayments()->isEmpty() ||
            $cancelledOrderPayments->count() === $order->getPayments()->count()
        ) {
            return OrderPaymentTransitions::TRANSITION_PAY;
        }

        if ($completedPaymentTotal < $order->getTotal() && 0 < $completedPaymentTotal) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_PAY;
        }

        return null;
    }

    private function getAuthorizeTransition(Order $order): ?string
    {
        // Authorized payments
        $authorizedPayments = $order->getPaymentsByState(PaymentInterface::STATE_AUTHORIZED);
        $authorizedPaymentTotal = $this->getPaymentsAmountsByState($authorizedPayments);

        if (0 < $authorizedPayments->count() && $authorizedPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_AUTHORIZE;
        }

        if ($authorizedPaymentTotal < $order->getTotal() && 0 < $authorizedPaymentTotal) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_AUTHORIZE;
        }

        return null;
    }

    private function getRequestPaymentTransition(Order $order): ?string
    {
        // Processing payments
        $processingPayments = $order->getPaymentsByState(PaymentInterface::STATE_PROCESSING);
        $processingPaymentTotal = $this->getPaymentsAmountsByState($processingPayments);

        if (0 < $processingPayments->count() && $processingPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_REQUEST_PAYMENT;
        }

        return null;
    }

    private function getPaymentsAmountsByState(Collection $payments): int
    {
        $totalAmount = 0;
        foreach ($payments as $payment) {
            $totalAmount += $payment->getAmount();
        }

        return $totalAmount;
    }
}
