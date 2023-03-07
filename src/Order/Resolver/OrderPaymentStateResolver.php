<?php

declare(strict_types=1);

namespace App\Order\Resolver;

use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\StateResolver\StateResolverInterface;
use Webmozart\Assert\Assert;

final class OrderPaymentStateResolver implements StateResolverInterface
{
    private FactoryInterface $stateMachineFactory;
    private OrderPaymentStateTransitionResolver $orderPaymentStateTransitionResolver;

    public function __construct(
        FactoryInterface $stateMachineFactory,
        OrderPaymentStateTransitionResolver $orderPaymentStateTransitionResolver
    ) {
        $this->stateMachineFactory = $stateMachineFactory;
        $this->orderPaymentStateTransitionResolver = $orderPaymentStateTransitionResolver;
    }

    public function resolve(BaseOrderInterface $order): void
    {
        /** @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        $stateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);
        $targetTransition = $this->orderPaymentStateTransitionResolver->getTargetTransition($order);

        if (null !== $targetTransition) {
            $this->applyTransition($stateMachine, $targetTransition);
        }
    }

    private function applyTransition(StateMachineInterface $stateMachine, string $transition): void
    {
        if ($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
        }
    }
}
