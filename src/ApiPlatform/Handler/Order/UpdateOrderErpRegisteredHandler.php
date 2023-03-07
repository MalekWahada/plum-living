<?php

declare(strict_types=1);

namespace App\ApiPlatform\Handler\Order;

use App\ApiPlatform\Message\Order\UpdateErpRegistered;
use App\Entity\Order\Order;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class UpdateOrderErpRegisteredHandler implements MessageHandlerInterface
{
    private OrderRepositoryInterface $orderRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateErpRegistered $command): OrderInterface
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->findOneByTokenValue($command->orderTokenValue);

        Assert::notNull($order);

        $order->setErpRegistered($command->erpRegistered);

        $this->entityManager->flush();

        return $order;
    }
}
