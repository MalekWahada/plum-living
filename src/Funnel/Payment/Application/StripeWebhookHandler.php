<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application;

use App\Checkout\Delivery\UpdateDateDelivery;
use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Email\Emails;
use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Entity\Payment\Payment;
use App\Entity\Payment\PaymentMethod;
use App\Funnel\Payment\Application\Creator\PaymentCreator;
use App\Funnel\Payment\Application\Creator\PaymentIntentCreator;
use App\Funnel\Payment\Domain\Exception\DatabaseException;
use App\Funnel\Payment\Domain\Exception\EmailException;
use App\Funnel\Payment\Domain\Exception\EntityNotFoundException;
use App\Funnel\Payment\Domain\Exception\OverlyPaidException;
use App\Funnel\Payment\Domain\Exception\PartiallyPaidException;
use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\Exception\UnknownPaymentTypeException;
use Doctrine\Persistence\ObjectManager;
use Exception;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Stripe\Webhook;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\StateMachine\StateMachineInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class StripeWebhookHandler
{
    public const PAYMENT_PARTIALLY_FUNDED = 'payment_intent.partially_funded';
    public const CUSTOMER_BALANCE_FUNDED = 'customer.balance_funded';

    public const TYPE_MAPPING = [
        'payment_intent.succeeded' => 'completed',
    ];

    private FactoryInterface $factory;
    private ObjectManager $objectManager;
    private string $stripeSecret;
    private PaymentIntentCreator $paymentIntentCreator;
    private PaymentCreator $paymentCreator;
    private SenderInterface $emailSender;
    private UpdateDateDelivery $updateDateDelivery;
    private SwitchableTranslationContextInterface $translationContext;

    public function __construct(
        FactoryInterface $factory,
        ObjectManager $objectManager,
        string $stripeSecret,
        PaymentIntentCreator $paymentIntentCreator,
        PaymentCreator $paymentCreator,
        SenderInterface $emailSender,
        UpdateDateDelivery $updateDateDelivery,
        SwitchableTranslationContextInterface $translationContext
    ) {
        $this->factory = $factory;
        $this->objectManager = $objectManager;
        $this->stripeSecret = $stripeSecret;
        $this->paymentIntentCreator = $paymentIntentCreator;
        $this->paymentCreator = $paymentCreator;
        $this->emailSender = $emailSender;
        $this->updateDateDelivery = $updateDateDelivery;
        $this->translationContext = $translationContext;
    }

    /**
     * @param Request $request
     * @return array{'payment_method' : string|null, 'amount' : int|null, 'order_id' : mixed, 'order_number' : string|null}
     * @throws DatabaseException
     * @throws EmailException
     * @throws EntityNotFoundException
     * @throws OverlyPaidException
     * @throws PartiallyPaidException
     * @throws StripeException
     * @throws UnknownPaymentTypeException
     */
    public function __invoke(Request $request): array
    {
        $endpoint_secret = $this->stripeSecret;

        $payload = $request->getContent();
        \assert(is_string($payload));

        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $stripeEvent = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpoint_secret
            );
        } catch (Exception $e) {
            $context = [
                'error_type' => 'payload_error',
                'payload' => $payload,
                'sig_header' => $sigHeader,
                'endpoint_secret' => $endpoint_secret,
            ];

            throw new StripeException($e->getMessage(), $context, 0, $e);
        }

        if (self::CUSTOMER_BALANCE_FUNDED === $stripeEvent->offsetGet('type')) {
            $context = [
                'error_type' => 'overly_paid',
                'payment_intent_id' => $stripeEvent->offsetGet('data')->offsetGet('object')->offsetGet('id'),
            ];

            throw new OverlyPaidException(
                'A customer has wired an amount that exceed the total of the order',
                $context
            );
        }

        try {
            $paymentIntent = ($this->paymentIntentCreator)($stripeEvent);
            $payment = ($this->paymentCreator)($stripeEvent, $paymentIntent);
        } catch (StripeException $e) {
            $context = [
                'error_type' => 'payload_error',
                'payload' => $payload,
            ];

            throw new StripeException($e->getMessage(), $context, 0, $e);
        }

        if (self::PAYMENT_PARTIALLY_FUNDED === $payment->type) {
            $context = [
                'error_type' => 'partially_paid',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
            ];

            throw new PartiallyPaidException(
                'A customer has wired an amount that does not cover the total of the order',
                $context
            );
        }

        if (false === array_key_exists($payment->type, self::TYPE_MAPPING)) {
            $context = [
                'error_type' => 'unknown_payment_type',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
                'payment_type' => $payment->type,
            ];

            throw new UnknownPaymentTypeException('Stripe Event mapping not found', $context);
        }

        $order = $this->objectManager->getRepository(Order::class)->findOneBy(['id' => $paymentIntent->orderId]);
        if (!$order instanceof Order) {
            $context = [
                'error_type' => 'order_not_found',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
            ];

            throw new EntityNotFoundException('Order entity not found', $context);
        }

        $paymentEntity = $order->getLastPayment();
        if (!$paymentEntity instanceof Payment) {
            $context = [
                'error_type' => 'payment_not_found',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
            ];

            throw new EntityNotFoundException('Payment entity not found', $context);
        }

        $paymentMethod = $paymentEntity->getMethod();
        if (!$paymentMethod instanceof PaymentMethod) {
            $context = [
                'error_type' => 'payment_method_not_found',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
            ];

            throw new EntityNotFoundException('PaymentMethod entity not found', $context);
        }

        $customer = $order->getCustomer();
        if (!$customer instanceof Customer) {
            $context = [
                'error_type' => 'customer_not_found',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
            ];

            throw new EntityNotFoundException('Customer entity not found', $context);
        }

        $paymentEntity->setDetails((array)$paymentIntent);

        if (PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE === $paymentMethod->getCode()) {
            $paymentEntity->setWireDetails([]);
        }

        if (PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE === $paymentMethod->getCode()) {
            try {
                $this->translationContext->setCustomerContext($customer);
                $this->emailSender->send(
                    Emails::EMAIL_ORDER_WIRE_PAYMENT_VALIDATED, // TODO: use MailerListener to send email
                    [$customer->getEmail()],
                    [
                        'locale' => $order->getLocaleCode(),
                        'channelCode' => $order->getChannel()->getCode(),
                        'transSlug' => $this->translationContext->getSlug(),
                        'orderNumber' => $order->getNumber(),
                        'total' => $order->getTotal(),
                        'wire' => $paymentEntity->getWireDetails()
                    ]
                );
            } catch (Exception $e) {
                $context = [
                    'error_type' => 'email_not_sent',
                    'payment_intent_id' => $paymentIntent->id,
                    'order_id' => $paymentIntent->orderId,
                ];

                throw new EmailException('Email was not sent', $context);
            }
        }

        $this->updateDateDelivery->recalculateAndUpdateMinMaxDelays($order);

        try {
            $this->objectManager->persist($order);
            $this->updatePaymentState($paymentEntity, self::TYPE_MAPPING[$payment->type]);

            $this->objectManager->flush();

            return [
                'payment_method' => $paymentMethod->getCode(),
                'amount' => $paymentEntity->getAmount(),
                'order_id' => $order->getId(),
                'order_number' => $order->getNumber()
            ];
        } catch (Exception $e) {
            $context = [
                'error_type' => 'database_error',
                'payment_intent_id' => $paymentIntent->id,
                'order_id' => $paymentIntent->orderId,
            ];

            throw new DatabaseException('An error occurred with the database', $context);
        }
    }

    /**
     * @throws SMException
     */
    private function updatePaymentState(Payment $payment, string $nextState): void
    {
        $stateMachine = $this->factory->get($payment, PaymentTransitions::GRAPH);

        Assert::isInstanceOf($stateMachine, StateMachineInterface::class);

        if (null !== $transition = $stateMachine->getTransitionToState($nextState)) {
            $stateMachine->apply($transition);
        }
    }
}
