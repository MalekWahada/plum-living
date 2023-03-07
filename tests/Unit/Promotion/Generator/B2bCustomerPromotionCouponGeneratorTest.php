<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Generator;

use App\Entity\Customer\Customer;
use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionCoupon;
use App\Factory\Promotion\PromotionFactoryInterface;
use App\Promotion\Generator\B2bCustomerPromotionCouponGenerator;
use App\Repository\Promotion\PromotionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Promotion\Factory\PromotionCouponFactoryInterface;

/**
 * @covers \App\Promotion\Generator\CreateCouponForCustomer
 */
final class B2bCustomerPromotionCouponGeneratorTest extends TestCase
{
    /** @var MockObject&PromotionRepository */
    private $promotionRepository;

    /** @var MockObject&PromotionCouponFactoryInterface */
    private $promotionCouponFactory;

    /** @var MockObject&PromotionFactoryInterface */
    private $promotionFactory;

    public function __construct()
    {
        $this->promotionRepository = $this->createMock(PromotionRepository::class);
        $this->promotionFactory = $this->createMock(PromotionFactoryInterface::class);
        $this->promotionCouponFactory = $this->createMock(PromotionCouponFactoryInterface::class);
        parent::__construct();
    }

    public function testItCreatesPromotionWhenPromotionIsNotFound(): void
    {
        $this->promotionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => Promotion::B2B_PROGRAM_PROMOTION_CODE])
            ->willReturn(null);

        $this->promotionFactory->expects(self::once())
            ->method('createForB2bProgram');

        $this->promotionCouponFactory->expects(self::once())
            ->method('createForPromotion')
            ->willReturn(new PromotionCoupon());

        (new B2bCustomerPromotionCouponGenerator(
            $this->promotionRepository,
            $this->promotionFactory,
            $this->promotionCouponFactory,
        ))->generate(new Customer());
    }

    public function testItCreatesAValidCouponForACustomer(): void
    {
        $promotion = new Promotion();
        $coupon = new PromotionCoupon();
        $customer = new Customer();
        $customer->setFirstName('Firstname');
        $customer->setLastName('Lastname');
        $customer->setCompanyName('Incredible compañy N4m3');

        $this->promotionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => Promotion::B2B_PROGRAM_PROMOTION_CODE])
            ->willReturn($promotion);

        $this->promotionCouponFactory->expects(self::once())
            ->method('createForPromotion')
            ->with($promotion)
            ->willReturn($coupon);

        (new B2bCustomerPromotionCouponGenerator(
            $this->promotionRepository,
            $this->promotionFactory,
            $this->promotionCouponFactory,
        ))->generate($customer);

        self::assertSame($customer, $coupon->getCustomer());
        self::assertSame('TC_INCREDIBLECOMPANYN4M3_FL_00001', $coupon->getCode());
    }
}
