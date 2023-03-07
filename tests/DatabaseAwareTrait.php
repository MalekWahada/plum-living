<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User\ShopUser;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait DatabaseAwareTrait
{
    private EntityManagerInterface $entityManager;

    private function initDatabase(KernelInterface $kernel): void
    {
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function getShopUser(string $email): ?UserInterface
    {
        return $this
            ->entityManager
            ->getRepository(ShopUser::class)
            ->findOneBy(['username' => $email]);
    }
}
