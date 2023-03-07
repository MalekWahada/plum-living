<?php

declare(strict_types=1);

namespace App\Tests\Unit\Funnel\Payment\Application\Creator;

use App\Entity\User\ShopUser;
use App\Funnel\Payment\Application\Creator\StripeCustomerCreator;
use Doctrine\Persistence\ObjectManager;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

final class StripeCustomerCreatorTest extends TestCase
{
    /** @var LoggerInterface&Mock */
    private $logger;

    private ObjectManager $objectManager;

    private ShopUser $currentUser;

    private CustomerInterface $customerInterface;

    private StripeCustomerCreator $stripeCustomerCreator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->customerInterface = $this->createStub(CustomerInterface::class);
        $this->currentUser = $this->createMock(ShopUser::class);
        $this->stripeCustomerCreator = new StripeCustomerCreator($this->objectManager, $this->logger);
        parent::setUp();
    }

    public function testItRetrievesStripeIdFromDatabase(): void
    {
        $this->currentUser->method('getCustomer')->willReturn($this->customerInterface);

        $this->currentUser->expects(self::never())->method('setStripeId');

        $expectedStripeId = 'cus_KbiquZoyi58Df0';

        $this->currentUser->method('getStripeId')->willReturn($expectedStripeId);

        self::assertEquals($expectedStripeId, ($this->stripeCustomerCreator)($this->currentUser));
    }
}
