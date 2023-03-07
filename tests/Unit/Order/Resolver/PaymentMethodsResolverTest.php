<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order\Resolver;

use App\Entity\Channel\Channel;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Entity\Order\OrderItemUnit;
use App\Entity\Payment\Payment;
use App\Entity\Payment\PaymentMethod;
use App\Order\Resolver\PaymentMethodsResolver;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

class PaymentMethodsResolverTest extends TestCase
{
    private PaymentMethodsResolver $target;

    private PaymentMethodsResolverInterface $decorated;
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decorated = $this->createMock(PaymentMethodsResolverInterface::class);
        $this->decorated->method('supports')->willReturn(true);

        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->paymentMethodRepository->method('findEnabledForChannel')->willReturn($this->buildPaymentMethods());

        $this->target = new PaymentMethodsResolver($this->decorated, $this->paymentMethodRepository);
    }

    public function testCanCreate(): void
    {
        $this->assertNotNull($this->target);
        $this->assertInstanceOf(PaymentMethodsResolver::class, $this->target);
    }

    public function testChannelFranceBelowThreshold(): void
    {
        // Arrange
        $payment = $this->buildPayment(1500000, 'PLUM_FR'); // 1500 €

        // Act
        $result = $this->target->getSupportedMethods($payment);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals(PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE, $result[0]->getCode());
    }

    public function testChannelFranceAboveThreshold(): void
    {
        // Arrange
        $payment = $this->buildPayment(2200000, 'PLUM_FR'); // 2200 €

        // Act
        $result = $this->target->getSupportedMethods($payment);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals(PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE, $result[0]->getCode());
        $this->assertEquals(PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE, $result[1]->getCode());
    }

    public function testChannelEuropeBelowThreshold(): void
    {
        // Arrange
        $payment = $this->buildPayment(400000, 'PLUM_DE'); // 400 €

        // Act
        $result = $this->target->getSupportedMethods($payment);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals(PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE, $result[0]->getCode());
    }

    public function testChannelEuropeAboveThreshold(): void
    {
        // Arrange
        $payment = $this->buildPayment(610000, 'PLUM_DE'); // 610 €

        // Act
        $result = $this->target->getSupportedMethods($payment);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals(PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE, $result[0]->getCode());
        $this->assertEquals(PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE, $result[1]->getCode());
    }

    private function buildPayment(int $amount, string $orderChannelCode): PaymentInterface
    {
        $channel = new Channel();
        $channel->setCode($orderChannelCode);
        $order = new Order();

        $orderItem = new OrderItem();
        $orderItem->setUnitPrice($amount);
        $orderItem->addUnit(new OrderItemUnit($orderItem));

        $order->addItem($orderItem);
        $order->setChannel($channel);
        $payment = new Payment();
        $payment->setOrder($order);

        return $payment;
    }

    private function buildPaymentMethods(): array
    {
        $creditCard = new PaymentMethod();
        $creditCard->setCode(PaymentMethod::STRIPE_PAYMENT_METHOD_CARD_CODE);

        $wireTransfer = new PaymentMethod();
        $wireTransfer->setCode(PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE);

        return [$creditCard, $wireTransfer];
    }
}
