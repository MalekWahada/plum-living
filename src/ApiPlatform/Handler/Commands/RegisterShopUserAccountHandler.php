<?php

declare(strict_types=1);

namespace App\ApiPlatform\Handler\Commands;

use App\ApiPlatform\Message\Commands\RegisterShopUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\Component\User\Security\PasswordUpdaterInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * Create or update the account of a shop user by setting his password
 * Class RegisterShopUserAccountHandler
 * @package App\ApiPlatform\Handler\Commands
 */
final class RegisterShopUserAccountHandler implements MessageHandlerInterface
{
    private FactoryInterface $shopUserFactory;
    private EntityManagerInterface $entityManger;
    private UserRepositoryInterface $shopUserRepository;
    private CustomerRepositoryInterface $customerRepository;
    private PasswordUpdaterInterface $passwordUpdater;

    public function __construct(
        FactoryInterface $shopUserFactory,
        EntityManagerInterface $entityManager,
        UserRepositoryInterface $shopUserRepository,
        CustomerRepositoryInterface $customerRepository,
        PasswordUpdaterInterface $passwordUpdater
    ) {
        $this->shopUserFactory = $shopUserFactory;
        $this->entityManger = $entityManager;
        $this->shopUserRepository = $shopUserRepository;
        $this->customerRepository = $customerRepository;
        $this->passwordUpdater = $passwordUpdater;
    }

    public function __invoke(RegisterShopUserAccount $command): void
    {
        Assert::notNull($command->getCustomerId());
        Assert::notNull($command->getPassword());

        /** @var CustomerInterface|null $customer */
        $customer = $this->customerRepository->find($command->getCustomerId());
        if ($customer === null || $customer->getEmail() === null) {
            throw new \InvalidArgumentException(sprintf('This customer does not exist.'));
        }

        /** @var ShopUserInterface|null $user */
        $user = $this->shopUserRepository->findOneBy(["customer" => $customer->getId()]);

        if ($user === null) { // Add new user account from customer
            /** @var ShopUserInterface $user */
            $user = $this->shopUserFactory->createNew();
            $user->setCustomer($customer);
        }

        // Update password
        $user->setPlainPassword($command->getPassword());
        $this->passwordUpdater->updatePassword($user);

        // (Optional fields) Account enabled and email verified
        if ($command->getEnabled() !== null) {
            $command->getEnabled() ? $user->enable() : $user->disable();
        }
        if ($command->getEmailVerified() !== null) {
            if ($user->getVerifiedAt() === null && $command->getEmailVerified()) {
                $user->setVerifiedAt(new \DateTime());
            } elseif (!$command->getEmailVerified()) {
                $user->setVerifiedAt(null);
            }
        }

        $this->entityManger->persist($user);
        $this->entityManger->flush();
    }
}
