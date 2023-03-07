<?php

declare(strict_types=1);

namespace App\Order\Generator;

use Sylius\Bundle\OrderBundle\NumberGenerator\OrderNumberGeneratorInterface;
use Sylius\Component\Order\Model\OrderInterface;
use function sprintf;

class SequentialOrderNumberGenerator implements OrderNumberGeneratorInterface
{
    private OrderNumberGeneratorInterface $decoratedGenerator;

    public function __construct(OrderNumberGeneratorInterface $decoratedGenerator)
    {
        $this->decoratedGenerator = $decoratedGenerator;
    }

    public function generate(OrderInterface $order): string
    {
        return sprintf('%s%s', 'PL', $this->decoratedGenerator->generate($order));
    }
}
