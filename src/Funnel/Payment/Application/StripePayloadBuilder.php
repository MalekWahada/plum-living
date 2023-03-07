<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application;

use App\Entity\Order\Order;
use App\Entity\Payment\PaymentMethod;
use App\Funnel\Payment\Domain\Exception\UnsupportedPaymentMethodException;
use Exception;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class StripePayloadBuilder
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws Exception
     * @return array<int|string, mixed>
     */
    public function __invoke(Order $order, string $stripeId): array
    {
        if (null === $order->getLastPayment()
            || null === $order->getLastPayment()->getMethod()
        ) {
            $this->logger->error(
                sprintf(
                    'Missing Payment or PaymentMethod for order: #%s',
                    $order->getId()
                )
            );
            throw new UnexpectedValueException('Missing properties to process order');
        }

        if (!in_array(
            $order->getLastPayment()->getMethod()->getCode(),
            PaymentMethod::AVAILABLE_PAYMENT_METHOD_CODE,
            true
        )) {
            throw new UnsupportedPaymentMethodException();
        }

        switch ($order->getLastPayment()->getMethod()->getCode()) {
            case PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE:
                return $this->buildCardPayload($order, $stripeId);
            case PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE:
                return $this->buildWirePayload($order, $stripeId);
        }

        throw new UnsupportedPaymentMethodException();
    }

    /**
     * @return array<int|string, mixed>
     */
    private function buildCardPayload(Order $order, string $stripeId): array
    {
        return [
            'amount' => (int) round($order->getTotal()/10),
            'currency' => $order->getCurrencyCode(),
            'customer' => $stripeId,
            'payment_method_types' => [
                'card'
            ],
            'metadata' => [
                'order_id' => $order->getId()
            ],
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function buildWirePayload(Order $order, string $stripeId): array
    {
        return [
            'amount' => (int) round($order->getTotal()/10),
            'currency' => $order->getCurrencyCode(),
            'customer' => $stripeId,
            'payment_method_types' => [
                'customer_balance',
            ],
            'payment_method_data' => [
                'type' => 'customer_balance',
            ],
            'payment_method_options' => [
                'customer_balance' => [
                    'funding_type' => 'bank_transfer',
                    'bank_transfer' => [
                        'type' => 'eu_bank_account',
                        'eu_bank_account' => [
                            'country' => 'FR'
                        ]
                    ],
                ],
            ],
            'confirm' => true,
            'metadata' => [
                'order_id' => $order->getId()
            ],
        ];
    }
}
