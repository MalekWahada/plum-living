<?php

declare(strict_types=1);

namespace App\Promotion\Generator;

use App\Entity\Customer\Customer;
use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionCoupon;
use App\Factory\Promotion\PromotionFactoryInterface;
use App\Provider\Promotion\PromotionProvider;
use App\Repository\Promotion\PromotionRepository;
use App\Repository\Promotion\PromotionRepositoryInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Promotion\Factory\PromotionCouponFactoryInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class B2bCustomerPromotionCouponGenerator
{
    private PromotionCouponFactoryInterface $promotionCouponFactory;
    private PromotionRepositoryInterface $promotionRepository;
    private PromotionFactoryInterface $promotionFactory;

    public function __construct(
        PromotionRepositoryInterface $promotionRepository,
        PromotionFactoryInterface $promotionFactory,
        PromotionCouponFactoryInterface $promotionCouponFactory
    ) {
        $this->promotionRepository = $promotionRepository;
        $this->promotionFactory = $promotionFactory;
        $this->promotionCouponFactory = $promotionCouponFactory;
    }

    /**
     * @throws \Exception
     */
    public function generate(Customer $customer): PromotionCoupon
    {
        $promotion = $this->promotionRepository->findOneBy(['code' => Promotion::B2B_PROGRAM_PROMOTION_CODE]);

        if (null === $promotion) {
            $promotion = $this->promotionFactory->createForB2bProgram();
            $this->promotionRepository->add($promotion);
        }

        $coupon = $this->promotionCouponFactory->createForPromotion($promotion);
        \assert($coupon instanceof PromotionCoupon);

        preg_match_all('/(?<=\s|^)[a-z]/i', $customer->getFullName(), $matches); // Get first letter of each word

        $slugger = new AsciiSlugger();
        $coupon->setCode(
            'TC_'.
            strtoupper((string) $slugger->slug((string) $customer->getCompanyName(), '')).'_'.
            strtoupper((string) $slugger->slug(implode('', $matches[0]))).'_'.
            sprintf('%05d', ($promotion->getCoupons()->count()+1))
        );
        $coupon->setCustomer($customer);

        return $coupon;
    }
}
