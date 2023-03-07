<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Domain;

class Payment
{
    public string $id;

    public string $type;

    public string $object;

    public PaymentIntent $paymentIntent;

    public function __construct(
        string $id,
        string $type,
        string $object,
        PaymentIntent $paymentIntent
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->object = $object;
        $this->paymentIntent = $paymentIntent;
    }
}
