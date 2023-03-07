<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Application\Creator;

use App\Funnel\Payment\Domain\Exception\MissingCustomerException;
use App\Funnel\Payment\Domain\Exception\StripeException;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Psr\Log\LoggerInterface;
use Stripe\Customer as StripeCustomer;
use App\Entity\User\ShopUser;

final class StripeCustomerCreator
{
    private ObjectManager $objectManager;

    private LoggerInterface $logger;

    public function __construct(
        ObjectManager $objectManager,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * @throws MissingCustomerException
     * @throws StripeException
     */
    public function __invoke(ShopUser $currentUser): string
    {
        if (null === $currentUser->getCustomer()) {
            throw new MissingCustomerException();
        }

        if ($currentUser->getStripeId()) {
            return $currentUser->getStripeId();
        }

        try {
            $stripeCustomer = StripeCustomer::create(
                [
                    'email' => $currentUser->getUsername(),
                    'metadata' => [
                        'customer_id' => $currentUser->getCustomer()->getId(),
                    ],
                ]
            );
        } catch (Exception $e) {
            $this->logger->error(
                sprintf(
                    '[Stripe] An occurred during stripe customer creation for User Id: #%s',
                    $currentUser->getId()
                )
            );

            throw new StripeException();
        }

        $currentUser->setStripeId($stripeCustomer->id);

        $this->objectManager->persist($currentUser);
        $this->objectManager->flush();

        return $currentUser->getStripeId();
    }
}
