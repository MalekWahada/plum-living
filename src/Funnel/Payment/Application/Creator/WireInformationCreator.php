<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application\Creator;

use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\WireInformation;
use Psr\Log\LoggerInterface;
use Stripe\PaymentIntent;

final class WireInformationCreator
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws StripeException
     */
    public function __invoke(PaymentIntent $paymentIntent): WireInformation
    {
        if (null === ($nextAction = $paymentIntent->offsetGet('next_action'))) {
            $this->logger->error('[Payment] Missing property: "next_action"');
            throw new StripeException('Mandatory field is missing');
        }

        if (null === ($displayInstruction = $nextAction->offsetGet('display_bank_transfer_instructions'))) {
            $this->logger->error('[Payment] Missing property: "display_bank_transfer_instructions"');
            throw new StripeException('Mandatory field is missing');
        }

        if (null === ($financialAddresse = current($displayInstruction->offsetGet('financial_addresses')))) {
            $this->logger->error('[Payment] Missing property: "financial_addresses"');
            throw new StripeException('Mandatory field is missing');
        }

        if (null === ($iban = $financialAddresse->offsetGet('iban'))) {
            $this->logger->error('[Payment] Missing property: "iban"');
            throw new StripeException('Mandatory field is missing');
        }

        return new WireInformation(
            $displayInstruction->offsetGet('reference'),
            $iban->offsetGet('account_holder_name'),
            $iban->offsetGet('bic'),
            $iban->offsetGet('country'),
            $iban->offsetGet('iban')
        );
    }
}
