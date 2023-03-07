<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application\Creator;

use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\Payment;
use App\Funnel\Payment\Domain\PaymentIntent;
use ArrayAccess;
use Psr\Log\LoggerInterface;

final class PaymentCreator
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /** @throws StripeException */
    public function __invoke(ArrayAccess $stripeEvent, PaymentIntent $paymentIntent): Payment
    {
        if (!$stripeEvent->offsetExists('id')
            || !($id = $stripeEvent->offsetGet('id'))) {
            $this->logger->error('[Payment] Missing property: "id"');
            throw new StripeException('Mandatory id field is missing');
        }

        if (!$stripeEvent->offsetExists('type')
            || !($type = $stripeEvent->offsetGet('type'))) {
            $this->logger->error('[Payment] Missing property: "type"');
            throw new StripeException('Mandatory type field is missing');
        }

        if (!$stripeEvent->offsetExists('object')
            || !($object = $stripeEvent->offsetGet('object'))) {
            $this->logger->error('[Payment] Missing property: "object"');
            throw new StripeException('Mandatory object field is missing');
        }

        return new Payment(
            $id,
            $type,
            $object,
            $paymentIntent
        );
    }
}
