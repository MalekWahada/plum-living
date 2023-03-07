<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application\Creator;

use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\PaymentIntent;
use ArrayAccess;
use Psr\Log\LoggerInterface;

final class PaymentIntentCreator
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws StripeException
     */
    public function __invoke(ArrayAccess $stripeEvent): PaymentIntent
    {
        if (!$stripeEvent->offsetExists('data')
            || !($data = $stripeEvent->offsetGet('data'))) {
            $this->logger->error('[Payment] Missing property: "data"');
            throw new StripeException('Mandatory data field is missing');
        }

        if (!$data->offsetExists('object')
            || !($stripeObject = $data->offsetGet('object'))) {
            $this->logger->error('[Payment] Missing property: "object"');
            throw new StripeException('Mandatory object field is missing');
        }

        if (!$stripeObject->offsetExists('id')
            || !($id = $stripeObject->offsetGet('id'))) {
            $this->logger->error('[Payment] Missing property: "id"');
            throw new StripeException('Mandatory id field is missing');
        }

        if (!$stripeObject->offsetExists('object')
            || !($object = $stripeObject->offsetGet('object'))) {
            $this->logger->error('[Payment] Missing property: "object"');
            throw new StripeException('Mandatory object field is missing');
        }

        if (!$stripeObject->offsetExists('amount')
            || !($amount = $stripeObject->offsetGet('amount'))) {
            $this->logger->error('[Payment] Missing property: "amount"');
            throw new StripeException('Mandatory amount field is missing');
        }

        if (!$stripeObject->offsetExists('payment_method_types')
            || !($amounts = $stripeObject->offsetGet('payment_method_types'))
            || !(isset($amounts[0]))
        ) {
            $this->logger->error('[Payment] Missing property: "payment_method_types"');
            throw new StripeException('Mandatory payment_method_types field is missing');
        }

        if (!$stripeObject->offsetExists('metadata')
            || !($metadata = $stripeObject->offsetGet('metadata'))
        ) {
            $this->logger->error('[Payment] Missing property: "metadata"');
            throw new StripeException('Mandatory metadata field is missing');
        }

        if (!$metadata->offsetExists('order_id')
            || !($orderId = $metadata->offsetGet('order_id'))
        ) {
            $this->logger->error('[Payment] Missing property: "order_id"');
            throw new StripeException('Mandatory order_id field is missing');
        }

        return new PaymentIntent(
            $id,
            $object,
            $amount,
            $amounts[0],
            $orderId
        );
    }
}
