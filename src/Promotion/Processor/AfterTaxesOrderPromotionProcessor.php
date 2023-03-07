<?php

declare(strict_types=1);

namespace App\Promotion\Processor;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Webmozart\Assert\Assert;

final class AfterTaxesOrderPromotionProcessor implements OrderProcessorInterface
{
    private AfterTaxesPromotionProcessor $promotionProcessor;

    public function __construct(AfterTaxesPromotionProcessor $promotionProcessor)
    {
        $this->promotionProcessor = $promotionProcessor;
    }

    public function process(BaseOrderInterface $order): void
    {
        /** @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->promotionProcessor->process($order);
    }
}
