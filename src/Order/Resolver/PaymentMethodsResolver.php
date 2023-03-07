<?php

declare(strict_types=1);

namespace App\Order\Resolver;

use App\Entity\Channel\Channel;
use App\Entity\Payment\PaymentMethod;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Webmozart\Assert\Assert;

class PaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodRepositoryInterface $paymentMethodRepository;
    private PaymentMethodsResolverInterface $decorated;

    public function __construct(
        PaymentMethodsResolverInterface $decorated,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->decorated = $decorated;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getSupportedMethods(PaymentInterface $payment): array
    {
        /** @var \Sylius\Component\Core\Model\PaymentInterface $payment */
        Assert::isInstanceOf($payment, PaymentInterface::class);
        Assert::true($this->supports($payment), 'This payment method is not support by resolver');

        $methodsEnabledForChannel = $this->paymentMethodRepository->findEnabledForChannel(
            $payment->getOrder()->getChannel()
        );

        /** @var PaymentMethod $method */
        foreach ($methodsEnabledForChannel as $index => $method) {
            if ($method->getCode() === PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE) {
                // todo get threshold from proper config
                // Removing wire transfer payment method for France order with total less than 2000
                if ($payment->getOrder()->getChannel()->getCode() === Channel::DEFAULT_CODE &&
                    $payment->getOrder()->getTotal() < PaymentMethod::PAYMENT_WIRE_FRANCE_THRESHOLD) {
                    unset($methodsEnabledForChannel[$index]);
                }
                // Removing wire transfer payment method for all other EU countries with total less than 500
                if ($payment->getOrder()->getChannel()->getCode() !== Channel::DEFAULT_CODE &&
                    $payment->getOrder()->getTotal() < PaymentMethod::PAYMENT_WIRE_EUROPE_THRESHOLD) {
                    unset($methodsEnabledForChannel[$index]);
                }
            }
        }

        return $methodsEnabledForChannel;
    }

    public function supports(PaymentInterface $payment): bool
    {
        return $this->decorated->supports($payment);
    }
}
