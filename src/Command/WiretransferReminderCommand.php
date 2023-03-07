<?php

declare(strict_types=1);

namespace App\Command;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Entity\Payment\Payment;
use App\EventListener\RouterContextSwitchableTranslationSlugListener;
use App\Model\Translation\SwitchableTranslation;
use App\Repository\Payment\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Noksi\SyliusPlumHubspotPlugin\Dto\TransactionalEmailPayload;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Noksi\SyliusPlumHubspotPlugin\Service\HubspotTransactionnal;
use Symfony\Component\Routing\RouterInterface;

class WiretransferReminderCommand extends Command
{
    protected static $defaultName = 'app:wiretransfer-reminder';

    private PaymentRepository                $paymentRepository;
    private HubspotTransactionnal            $hubspotTransactional;
    private RouterInterface                  $router;
    private array                            $hubspotTransactionalConfig;
    private EntityManagerInterface           $em;
    private SwitchableTranslationContextInterface $translationContext;

    public function __construct(
        PaymentRepository      $paymentRepository,
        HubspotTransactionnal  $hubspotTransactional,
        RouterInterface        $router,
        EntityManagerInterface $em,
        SwitchableTranslationContextInterface $translationContext,
        array                  $hubspotTransactionalConfig = [],
        string                 $name = null
    ) {
        parent::__construct($name);
        $this->paymentRepository = $paymentRepository;
        $this->hubspotTransactional = $hubspotTransactional;
        $this->hubspotTransactionalConfig = $hubspotTransactionalConfig;
        $this->router = $router;
        $this->em = $em;
        $this->translationContext = $translationContext;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('delay', null, InputOption::VALUE_REQUIRED, 'nb days to pass before remind', 7);
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('env') !== 'prod') {
            $output->writeln('<comment>not running in prod environment (--env=prod) can make Sylius Channel unloadable (Doctrine Hydratation bug)</comment>');
        }

        $delay = $input->getOption('delay');
        $limitDate = (new \DateTime())->sub(new \DateInterval('P'.$delay.'D'));

        /**
         * @var Payment[] $payments
         */
        $payments = $this->paymentRepository->getWiretransferToRemind($limitDate);
        foreach ($payments as $payment) {
            // mise à jour du router pour bonne génération du mail
            /** @var Order $order */
            $order = $payment->getOrder();
            /** @var ChannelInterface $channel */
            $channel = $order->getChannel();
            $context = $this->router->getContext();
            $context->setHost($channel->getHostname());

            /** @var Customer $customer */
            $customer = $order->getCustomer();

            // get slug from customer
            $this->translationContext->setCustomerContext($customer);
            $slug = $this->translationContext->getSlug();
            $context->setParameter(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER, $slug);

            $payload = new TransactionalEmailPayload();
            $payload->setRecipient($customer->getUser()->getEmail());

            $emailId = $this->hubspotTransactionalConfig['wire_payment_reminder']['hubspot_email_id'][$customer->getLocaleCode()] ?? 0;
            if ($emailId === 0) {
                continue;
            }

            $payload->setEmailId($this->hubspotTransactionalConfig['wire_payment_reminder']['hubspot_email_id']);
            $data = [
                'locale'      => $customer->getLocaleCode(),
                'channelCode' => $customer->getChannelCode(),
                'orderNumber' => $payment->getOrder()->getNumber(),
                'total'       => $payment->getOrder()->getTotal(),
                'wire'        => $payment->getWireDetails(),
                'transSlug'   => $slug,
            ];
            $this->hubspotTransactional->buildPayload('wire_payment_reminder', $payload, $data, []);
            $success = $this->hubspotTransactional->sendTransactional($payload);
            if ($success) {
                $payment->setRemindedAt(new \DateTime());
                $this->em->flush();
            }
        }
        return 0;
    }
}
