<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Entity\Taxonomy\Taxon;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class OrderCompleteListener
{
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Assign a default firstName and lastName to a customer when an order is made (if not already set)
     * because exporting order to ERP is requesting a non null field
     * @param ResourceControllerEvent $event
     */
    public function setCustomerDefaultInfo(ResourceControllerEvent $event): void
    {
        /** @var OrderInterface|null $order */
        $order = $event->getSubject();

        Assert::notNull($order);

        // Update customer account based on his billing address
        if (null  !== ($customer = $order->getCustomer()) && null !== ($address = $order->getBillingAddress())) {
            if (null === $customer->getFirstName()) {
                $customer->setFirstName($address->getFirstName());
            }

            if (null === $customer->getLastName()) {
                $customer->setLastName($address->getLastName());
            }
        }
    }

    public function setCustomerPreferredChannelAndLocaleCode(ResourceControllerEvent $event): void
    {
        /** @var OrderInterface|null $order */
        $order = $event->getSubject();

        Assert::notNull($order);

        /** @var ?Customer $customer */
        $customer = $order->getCustomer();

        if (null !== $customer) {
            try {
                $customer->setChannelCode($this->channelContext->getChannel()->getCode());
            } catch (ChannelNotFoundException $e) {
                $this->logger->error(sprintf('Unable to set channel (channel not found) for customer %s', $customer->getEmail()));
            }
            $customer->setLocaleCode($this->localeContext->getLocaleCode());

            $this->eventDispatcher->dispatch(new ResourceControllerEvent($customer), 'sylius.customer.post_update'); // Dispatch event to update Hubspot contact
        }
    }

    /**
     * Determine mailing workflow type based on order items taxon
     * @param ResourceControllerEvent $event
     */
    public function setMailingWorkflowType(ResourceControllerEvent $event): void
    {
        /** @var Order|null $order */
        $order = $event->getSubject();

        Assert::notNull($order);

        $hasMethodItems = false;
        $hasPaxItems = false;

        foreach ($order->getItems() as $item) {
            if (!$hasMethodItems && $item->getProduct()->isType(Taxon::TAXON_FACADE_METOD)) {
                $hasMethodItems = true;
            }
            if (!$hasPaxItems && $item->getProduct()->isType(Taxon::TAXON_FACADE_PAX)) {
                $hasPaxItems = true;
            }
        }

        if ($hasMethodItems && $hasPaxItems) {
            $order->setMailingWorkflowType(Order::MAILING_WORKFLOW_TYPE_METHOD_AND_PAX);
        } elseif ($hasMethodItems && !$hasPaxItems) { /** @phpstan-ignore-line */
            $order->setMailingWorkflowType(Order::MAILING_WORKFLOW_TYPE_METHOD);
        } elseif (!$hasMethodItems && $hasPaxItems) {
            $order->setMailingWorkflowType(Order::MAILING_WORKFLOW_TYPE_PAX);
        } else {
            $order->setMailingWorkflowType(Order::MAILING_WORKFLOW_TYPE_NOT_FRONT);
        }
    }
}
