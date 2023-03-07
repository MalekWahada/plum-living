<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Entity\Customer\Customer;
use App\Entity\Promotion\PromotionCoupon;
use App\EventListener\CustomerB2bProgramListener;
use App\Factory\Promotion\PromotionFactoryInterface;
use App\Promotion\Generator\B2bCustomerPromotionCouponGenerator;
use App\Repository\Promotion\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Promotion\Factory\PromotionCouponFactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @covers \App\EventListener\CustomerB2bProgramListener
 */
final class CustomerB2bProgramListenerTest extends TestCase
{
    private CustomerB2bProgramListener $listener;

    /** @var MockObject&PromotionRepository */
    private $promotionRepository;

    /** @var MockObject&LoggerInterface */
    private $logger;

    /** @var MockObject&EntityManagerInterface */
    private $entityManager;
    private B2bCustomerPromotionCouponGenerator $couponGenerator;

    /** @var PromotionFactoryInterface&MockObject */
    private PromotionFactoryInterface $promotionFactory;

    public function __construct()
    {
        $this->promotionRepository = $this->createMock(PromotionRepository::class);
        $this->promotionFactory = $this->createMock(PromotionFactoryInterface::class);
        $this->couponGenerator = new B2bCustomerPromotionCouponGenerator(
            $this->promotionRepository,
            $this->promotionFactory,
            $this->createStub(PromotionCouponFactoryInterface::class),
            'dummy_string'
        );
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new CustomerB2bProgramListener(
            $this->couponGenerator,
            $this->logger,
            $this->entityManager
        );
        parent::__construct();
    }

    public function testItDoesNothingWhenEventIsNotAboutCustomer(): void
    {
        $dummyObject = new class {
            public function hasB2bProgramOrIsEligibleToB2bProgram(): void
            {
                throw new Exception('hasB2bProgramOrIsEligibleToB2bProgram is not supposed to be called now');
            }
        };

        $event = $this->createMock(GenericEvent::class);
        $event->expects(self::once())->method('getSubject')->willReturn($dummyObject);
        $this->promotionRepository->expects(self::never())->method('findOneBy');

        $this->listener->generateB2bProgramCoupon($event);
    }

    public function testItDoesNothingWhenCustomerHasNoAccessToB2BProgram(): void
    {
        $dummyObject = new class extends Customer {
            public function hasB2bProgramOrIsEligibleToB2bProgram(): bool
            {
                return false;
            }
        };

        $event = $this->createMock(GenericEvent::class);
        $event->expects(self::once())->method('getSubject')->willReturn($dummyObject);
        $this->promotionRepository->expects(self::never())->method('findOneBy');

        $this->listener->generateB2bProgramCoupon($event);
    }

    public function testItDoesNothingWhenCustomerIsNotLinkedToACoupon(): void
    {
        $dummyObject = new class extends Customer {
            public function hasB2bProgramOrIsEligibleToB2bProgram(): bool
            {
                return true;
            }

            public function getPersonalCoupon(): ?PromotionCoupon
            {
                return new PromotionCoupon();
            }
        };

        $event = $this->createMock(GenericEvent::class);
        $event->expects(self::once())->method('getSubject')->willReturn($dummyObject);
        $this->promotionRepository->expects(self::never())->method('findOneBy');

        $this->listener->generateB2bProgramCoupon($event);
    }

    public function testItLogsOnException(): void
    {
        $dummyObject = new class extends Customer {
            public function getId(): int
            {
                return -1;
            }

            public function hasB2BProgram(): bool
            {
                return true;
            }

            public function getPersonalCoupon(): ?PromotionCoupon
            {
                return null;
            }
        };

        $event = $this->createMock(GenericEvent::class);
        $event->expects(self::once())->method('getSubject')->willReturn($dummyObject);

        $this->promotionRepository->expects(self::once())
            ->method('findOneBy')
            ->willThrowException(new Exception('Dummy exception'));

        $this->logger->expects(self::once())
            ->method('critical')
            ->with('Failed to register customer #-1 in the B2B program: Dummy exception');

        $this->listener->generateB2bProgramCoupon($event);
    }
}
