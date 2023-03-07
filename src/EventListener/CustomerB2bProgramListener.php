<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Customer\Customer;
use App\Promotion\Generator\B2bCustomerPromotionCouponGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CustomerB2bProgramListener
{
    private B2bCustomerPromotionCouponGenerator $couponGenerator;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(
        B2bCustomerPromotionCouponGenerator $couponGenerator,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->couponGenerator = $couponGenerator;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function generateB2bProgramCoupon(GenericEvent $event): void
    {
        $customer = $event->getSubject();
        if (!$customer instanceof Customer) {
            return;
        }

        // Create coupon for customer
        if ($customer->hasB2BProgram()) {
            if (null === $customer->getPersonalCoupon()) {
                try {
                    $coupon = $this->couponGenerator->generate($customer);
                    $customer->setPersonalCoupon($coupon);

                    $this->entityManager->persist($coupon);
                } catch (\Exception $exception) {
                    $this->logger->critical(sprintf(
                        'Failed to register customer #%d in the B2B program: %s',
                        $customer->getId(),
                        $exception->getMessage()
                    ));
                    return;
                }
            }

            $customer->getPersonalCoupon()->setExpiresAt(null); // Reset expiration if we reactivate B2B program to a customer
        }

        // Remove coupon if customer is no longer in the B2B program
        if (!$customer->hasB2BProgram() && null !== $customer->getPersonalCoupon()) {
            try {
                if ($customer->getPersonalCoupon()->getUsed() === 0) {
                    $this->entityManager->remove($customer->getPersonalCoupon());
                } else {
                    $customer->getPersonalCoupon()->setExpiresAt(new \DateTime()); // We cannot remove the coupon if it has been used. Set expired
                }
            } catch (\Exception $exception) {
                $this->logger->critical(sprintf(
                    'Failed to delete promotion coupon from the B2B program for customer %d: %s',
                    $customer->getId(),
                    $exception->getMessage()
                ));
            }
        }
    }
}
