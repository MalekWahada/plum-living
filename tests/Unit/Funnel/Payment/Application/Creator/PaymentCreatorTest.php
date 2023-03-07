<?php

declare(strict_types=1);

namespace App\Tests\Unit\Funnel\Payment\Application\Creator;

use App\Funnel\Payment\Application\Creator\PaymentCreator;
use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\Payment;
use App\Funnel\Payment\Domain\PaymentIntent;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class PaymentCreatorTest extends TestCase
{
    /** @var LoggerInterface&Mock */
    private $logger;

    private PaymentCreator $paymentCreator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->paymentCreator = new PaymentCreator($this->logger);
        parent::setUp();
    }

    public function testItCreatesAPayment(): void
    {
        $stripeEvent = new \ArrayObject([
            'id' => 'stripe_event_id',
            'type' => 'stripe_event_type',
            'object' => 'stripe_event_payment_intent'
        ]);

        $paymentIntent = $this->createStub(PaymentIntent::class);
        $expectedPayment = new Payment(
            'stripe_event_id',
            'stripe_event_type',
            'stripe_event_payment_intent',
            $paymentIntent
        );

        self::assertEquals($expectedPayment, ($this->paymentCreator)($stripeEvent, $paymentIntent));
    }

    /** @dataProvider itFailsToCreatePaymentDataProvider */
    public function testItFailsToCreateAPayment(array $array, string $message): void
    {
        $stripeEvent = new \ArrayObject($array);
        $paymentIntent = $this->createStub(PaymentIntent::class);
        $this->logger->expects(self::once())->method('error');
        $this->expectException(StripeException::class);
        $this->expectExceptionMessage($message);
        ($this->paymentCreator)($stripeEvent, $paymentIntent);
    }

    public function itFailsToCreatePaymentDataProvider(): \Generator
    {
        yield 'fails_on_empty_array' => [[], 'Mandatory id field is missing'];
        yield 'fails_on_empty_type' => [['id' => 'stripe_event_id'], 'Mandatory type field is missing'];
        yield 'fails_on_empty_object' => [['id' => 'stripe_event_id', 'type' => 'stripe_event_type'], 'Mandatory object field is missing'];
        yield 'fails_on_empty_string_id' => [['id' => ''], 'Mandatory id field is missing'];
        yield 'fails_on_empty_string_type' => [['id' => 'stripe_event_id', 'type' => ''], 'Mandatory type field is missing'];
        yield 'fails_on_empty_string_object' => [['id' => 'stripe_event_id', 'type' => 'stripe_event_type', 'object' => ''], 'Mandatory object field is missing'];
        yield 'fails_on_null_id' => [['id' => null], 'Mandatory id field is missing'];
        yield 'fails_on_null_type' => [['id' => 'stripe_event_id', 'type' => null], 'Mandatory type field is missing'];
        yield 'fails_on_null_object' => [['id' => 'stripe_event_id', 'type' => 'stripe_event_type', 'object' => null], 'Mandatory object field is missing'];
    }
}
