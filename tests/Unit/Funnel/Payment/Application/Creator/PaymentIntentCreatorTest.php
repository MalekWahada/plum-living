<?php

declare(strict_types=1);

namespace App\Tests\Unit\Funnel\Payment\Application\Creator;

use App\Funnel\Payment\Application\Creator\PaymentIntentCreator;
use App\Funnel\Payment\Domain\Exception\StripeException;
use App\Funnel\Payment\Domain\PaymentIntent;
use ArrayObject;
use Generator;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class PaymentIntentCreatorTest extends TestCase
{
    /** @var LoggerInterface&Mock */
    private $logger;

    private PaymentIntentCreator $paymentIntentCreator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->paymentIntentCreator = new PaymentIntentCreator($this->logger);
        parent::setUp();
    }

    public function testItCreatesAPaymentIntent(): void
    {
        $stripeEvent = new ArrayObject([
            'data' => new ArrayObject([
                'object' => new ArrayObject([
                    'id' => 'id',
                    'object' => 'object',
                    'amount' => 1234,
                    'payment_method_types' => new ArrayObject([
                        'payment_method_type'
                    ]),
                    'metadata' => new ArrayObject([
                        'order_id' => 'order_id'
                    ])
                ])
            ]),
        ]);

        $expectedPaymentIntent = new PaymentIntent(
            'id',
            'object',
            1234,
            'payment_method_type',
            'order_id'
        );

        self::assertEquals($expectedPaymentIntent, ($this->paymentIntentCreator)($stripeEvent));
    }

    /** @dataProvider itFailsToCreatePaymentIntentDataProvider */
    public function testItFailsToCreateAPaymentIntent(array $array, string $message): void
    {
        $stripeEvent = new ArrayObject($array);
        $this->logger->expects(self::once())->method('error');
        $this->expectException(StripeException::class);
        $this->expectExceptionMessage($message);
        ($this->paymentIntentCreator)($stripeEvent);
    }

    public function itFailsToCreatePaymentIntentDataProvider(): Generator
    {
        yield 'fails_on_empty_array' => [[], 'Mandatory data field is missing'];
        //data
        yield 'fails_on_null_data' => [['data' => null], 'Mandatory data field is missing'];
        yield 'fails_on_empty_data' => [['data' => ''], 'Mandatory data field is missing'];
        yield 'fails_on_empty_data_array' => [['data' => []], 'Mandatory data field is missing'];
        //stripe_object
        yield 'fails_on_null_stripe_object' => [['data' => new ArrayObject(['object' => null])], 'Mandatory object field is missing'];
        yield 'fails_on_empty_stripe_object' => [['data' => new ArrayObject(['object' => ''])], 'Mandatory object field is missing'];
        yield 'fails_on_empty_stripe_object_array' => [['data' => new ArrayObject(['object' => []])], 'Mandatory object field is missing'];
        //id
        yield 'fails_on_null_id' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => null])])], 'Mandatory id field is missing'];
        yield 'fails_on_empty_id' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => ''])])], 'Mandatory id field is missing'];
        yield 'fails_on_missing_id' => [['data' => new ArrayObject(['object' =>  new ArrayObject([])])], 'Mandatory id field is missing'];
        //object
        yield 'fails_on_null_object' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => null])])], 'Mandatory object field is missing'];
        yield 'fails_on_empty_object' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => ''])])], 'Mandatory object field is missing'];
        yield 'fails_on_missing_object' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id'])])], 'Mandatory object field is missing'];
        //amount
        yield 'fails_on_null_amount' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => null])])], 'Mandatory amount field is missing'];
        yield 'fails_on_empty_amount' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => ''])])], 'Mandatory amount field is missing'];
        yield 'fails_on_missing_amount' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object'])])], 'Mandatory amount field is missing'];
        //payment_method_types
        yield 'fails_on_null_payment_method_type' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => null])])], 'Mandatory payment_method_types field is missing'];
        yield 'fails_on_empty_payment_method_type' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => ''])])], 'Mandatory payment_method_types field is missing'];
        yield 'fails_on_missing_payment_method_type' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount'])])], 'Mandatory payment_method_types field is missing'];
        yield 'fails_on_empty_payment_method_type_array' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => []])])], 'Mandatory payment_method_types field is missing'];
        //metadata
        yield 'fails_on_null_metadata' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type']), 'metadata' => null])])], 'Mandatory metadata field is missing'];
        yield 'fails_on_empty_metadata' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type']), 'metadata' => ''])])], 'Mandatory metadata field is missing'];
        yield 'fails_on_missing_metadata' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type'])])])], 'Mandatory metadata field is missing'];
        yield 'fails_on_empty_metadata_array' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type']), 'metadata' => []])])], 'Mandatory metadata field is missing'];
        //order_id
        yield 'fails_on_null_order_id' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type']), 'metadata' => new ArrayObject(['order_id' => null])])])], 'Mandatory order_id field is missing'];
        yield 'fails_on_empty_order_id' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type']), 'metadata' => new ArrayObject(['order_id' => ''])])])], 'Mandatory order_id field is missing'];
        yield 'fails_on_missing_order_id' => [['data' => new ArrayObject(['object' =>  new ArrayObject(['id' => 'id', 'object' => 'object', 'amount' => 'amount', 'payment_method_types' => new ArrayObject(['payment_method_type']), 'metadata' => new ArrayObject()])])], 'Mandatory order_id field is missing'];
    }
}
