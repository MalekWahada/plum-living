<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application;

use App\Entity\Order\Order;
use App\Entity\Payment\Payment;
use App\Entity\Payment\PaymentMethod;
use App\Funnel\Payment\Application\Creator\StripeCustomerCreator;
use App\Funnel\Payment\Application\Creator\WireInformationCreator;
use App\Funnel\Payment\Domain\Exception\DatabaseException;
use App\Funnel\Payment\Domain\Exception\ForbiddenUserException;
use App\Funnel\Payment\Domain\Exception\MissingCustomerException;
use App\Funnel\Payment\Domain\Exception\MissingOrderException;
use App\Funnel\Payment\Domain\Exception\MissingPaymentException;
use App\Funnel\Payment\Domain\Exception\MissingPaymentMethodException;
use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\Exception\UserNotLoggedInException;
use App\Funnel\Payment\Domain\PaymentEntryAction;
use App\Provider\User\ShopUserProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Psr\Log\LoggerInterface;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

final class PaymentEntryHandler
{
    private string $stripeApiKey;
    private string $stripeApiVersion;
    private Session $session;
    private ObjectManager $objectManager;
    private StripePayloadBuilder $stripePayloadBuilder;
    private StripeCustomerCreator $stripeCustomerCreator;
    private WireInformationCreator $wireInformationCreator;
    private ShopUserProvider $userProvider;
    private LoggerInterface $logger;

    public function __construct(
        string                 $stripeApiKey,
        string                 $stripeApiVersion,
        Session                $session,
        ObjectManager          $objectManager,
        StripePayloadBuilder   $stripePayloadBuilder,
        StripeCustomerCreator  $stripeCustomerCreator,
        WireInformationCreator $wireInformationCreator,
        ShopUserProvider $userProvider,
        LoggerInterface $logger
    ) {
        $this->stripeApiKey = $stripeApiKey;
        $this->stripeApiVersion = $stripeApiVersion;
        $this->objectManager = $objectManager;
        $this->stripePayloadBuilder = $stripePayloadBuilder;
        $this->stripeCustomerCreator = $stripeCustomerCreator;
        $this->wireInformationCreator = $wireInformationCreator;
        $this->userProvider = $userProvider;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): PaymentEntryAction
    {
        Stripe::setApiKey($this->stripeApiKey);
        Stripe::setApiVersion($this->stripeApiVersion);

        $context = [
            'tokenValue' => $request->get('tokenValue'),
        ];

        $currentUser = $this->userProvider->getShopUser();
        if (!$currentUser) {
            $context['error_type'] = 'user_not_logged_in';

            throw new UserNotLoggedInException('User not logged in', $context);
        }

        $order = $this->objectManager->getRepository(Order::class)->findOneBy(['tokenValue' => $request->get('tokenValue')]);
        if (!$order) {
            $context['error_type'] = 'order_not_found';

            throw new MissingOrderException('Order not found', $context);
        }

        $orderCustomer = $order->getCustomer();
        if (!$orderCustomer) {
            $context['error_type'] = 'customer_not_found';

            throw new MissingCustomerException('Customer not found', $context);
        }

        $currentCustomer = $currentUser->getCustomer();
        if (!$currentCustomer) {
            $context['error_type'] = 'customer_not_found';
            throw new MissingCustomerException('Customer not found', $context);
        }

        if ($currentCustomer->getId() !== $orderCustomer->getId()) {
            $context['error_type'] = 'forbidden_user';
            $context['current_customer_id'] = $currentCustomer->getId();
            $context['order_customer_id'] = $orderCustomer->getId();
            throw new ForbiddenUserException('Forbidden user', $context);
        }

        $request->getSession()->set('sylius_order_id', $order->getId());

        if (0 === $order->getTotal()) {
            $this->logger->info(sprintf('Order "%d" has a total of 0', $order->getId()));
            return new PaymentEntryAction(
                PaymentEntryAction::ACTION_REDIRECT,
                'sylius_shop_order_thank_you'
            );
        }

        $payment = $order->getLastPayment();
        if (!$payment instanceof Payment) {
            $context['error_type'] = 'payment_not_found';

            throw new MissingPaymentException('Payment not found', $context);
        }

        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod) {
            $context['error_type'] = 'payment_method_not_found';

            throw new MissingPaymentMethodException('PaymentMethod not found', $context);
        }

        if (($payment->getWireDetails() && PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE === $paymentMethod->getCode())) {
            return new PaymentEntryAction(
                PaymentEntryAction::ACTION_REDIRECT,
                'sylius_wire_payment_success',
                [
                    'tokenValue' => $order->getTokenValue()
                ]
            );
        }

        try {
            $stripeId = ($this->stripeCustomerCreator)($currentUser);
        } catch (Exception $e) {
            $context['error_type'] = 'stripe_customer_creation_failed';
            throw new StripeException($e->getMessage(), $context, 0, $e);
        }

        try {
            $paymentIntent = PaymentIntent::create(($this->stripePayloadBuilder)($order, $stripeId));
        } catch (Exception $e) {
            $context['error_type'] = 'payment_intent_creation_failed';
            throw new StripeException($e->getMessage(), $context, 0, $e);
        }

        if ($paymentMethod->getCode() === PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE) {
            return new PaymentEntryAction(
                PaymentEntryAction::ACTION_RENDER,
                '@SyliusShop/Checkout/payment.html.twig',
                [
                    'clientSecret' => $paymentIntent->offsetGet('client_secret'),
                    'order' => $order,
                ]
            );
        }

        if ($paymentMethod->getCode() === PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE) {
            if ($payment->getWireDetails()) {
                return new PaymentEntryAction(
                    PaymentEntryAction::ACTION_REDIRECT,
                    'sylius_wire_payment_success',
                    ['tokenValue' => $order->getTokenValue()]
                );
            }

            try {
                $wireInformation = (array) ($this->wireInformationCreator)($paymentIntent);
                $payment->setWireDetails($wireInformation);
                $this->objectManager->persist($payment);
                $this->objectManager->flush();

                $this->session->getFlashBag()->set('wire_success_mail', 'true');
            } catch (StripeException $e) {
                $context['error_type'] = 'wire_details_flow_failed';
                throw new StripeException($e->getMessage(), $context, 0, $e);
            } catch (Exception $e) {
                $context['error_type'] = 'database_error';

                throw new DatabaseException($e->getMessage(), $context, 0, $e);
            }

            return new PaymentEntryAction(
                PaymentEntryAction::ACTION_REDIRECT,
                'sylius_wire_payment_success',
                ['tokenValue' => $order->getTokenValue()]
            );
        }

        return new PaymentEntryAction(
            PaymentEntryAction::ACTION_RENDER,
            '@Twig/Exception/error500.html.twig',
        );
    }
}
