<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use Sylius\Component\Payment\Model\PaymentMethodTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_method")
 */
class PaymentMethod extends BasePaymentMethod
{
    public const STRIPE_PAYMENT_METHOD_CARD_CODE = 'stripe_sca';
    public const STRIPE_PAYMENT_METHOD_WIRE_CODE = 'stripe_wire';

    // todo put this in adminable config
    public const PAYMENT_WIRE_EUROPE_THRESHOLD = 500000; // in milli cents
    public const PAYMENT_WIRE_FRANCE_THRESHOLD = 2000000; // in milli cents

    public const AVAILABLE_PAYMENT_METHOD_CODE = [
        self::STRIPE_PAYMENT_METHOD_CARD_CODE,
        self::STRIPE_PAYMENT_METHOD_WIRE_CODE
    ];

    protected function createTranslation(): PaymentMethodTranslationInterface
    {
        return new PaymentMethodTranslation();
    }
}
