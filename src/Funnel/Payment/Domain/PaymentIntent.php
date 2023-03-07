<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Domain;

class PaymentIntent
{
    public string $id;

    public string $object;

    public int $amount;

    public string $paymentMethodType;

    public string $orderId;

    public function __construct(
        string $id,
        string $object,
        int $amount,
        string $paymentMethodType,
        string $orderId
    ) {
        $this->id = $id;
        $this->object = $object;
        $this->amount = $amount;
        $this->paymentMethodType = $paymentMethodType;
        $this->orderId = $orderId;
    }
}
