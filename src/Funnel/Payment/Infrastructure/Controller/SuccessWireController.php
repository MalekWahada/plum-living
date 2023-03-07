<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Infrastructure\Controller;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Email\Emails;
use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Entity\Payment\Payment;
use App\Entity\User\ShopUser;
use Exception;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SuccessWireController extends AbstractController
{
    private ObjectManager $objectManager;
    private SenderInterface $emailSender;
    private LoggerInterface $logger;
    private Session $session;
    private SwitchableTranslationContextInterface $translationContext;


    public function __construct(
        ObjectManager   $objectManager,
        SenderInterface $emailSender,
        LoggerInterface $logger,
        Session $session,
        SwitchableTranslationContextInterface $translationContext
    ) {
        $this->objectManager = $objectManager;
        $this->emailSender = $emailSender;
        $this->logger = $logger;
        $this->session = $session;
        $this->translationContext = $translationContext;
    }

    public function __invoke(string $tokenValue, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof ShopUser) {
            throw new NotFoundHttpException('User not found');
        }

        $order = $this->objectManager->getRepository(Order::class)->findOneBy(['tokenValue' => $tokenValue]);
        if (!$order instanceof Order) {
            throw new NotFoundHttpException('Order not found');
        }

        $payment = $this->objectManager->getRepository(Payment::class)->findOneBy(['order' => $order->getId()]);
        if (!$payment instanceof Payment) {
            throw new NotFoundHttpException('Payment not found');
        }

        if ($this->session->getFlashBag()->get('wire_success_mail')) {
            $this->sendEmail($user, $order, $payment);
        }

        return $this->render('Shop/Order/wire_success.html.twig', [
            'order' => $order
        ]);
    }

    private function sendEmail(ShopUser $user, Order $order, Payment $payment): void
    {
        /** @var Customer $customer */
        $customer = $user->getCustomer();

        try {
            $this->translationContext->setCustomerContext($customer);
            $this->emailSender->send( // TODO: use MailerListener to send email
                Emails::EMAIL_ORDER_WIRE_PAYMENT_INFORMATION,
                [$user->getUsername()],
                [
                    'locale' => $order->getLocaleCode(),
                    'channelCode' => $order->getChannel()->getCode(),
                    'transSlug' => $this->translationContext->getSlug(),
                    'orderNumber' => $order->getNumber(),
                    'total' => $order->getTotal(),
                    'wire' => $payment->getWireDetails()
                ]
            );
        } catch (Exception $e) {
            $this->logger->critical(sprintf(
                '[Payment][Wire] An error occurred for order: #%s with the mailer: %s',
                $order->getId(),
                $e->getMessage()
            ));
        }
    }
}
