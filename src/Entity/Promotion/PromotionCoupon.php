<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use App\Entity\Customer\Customer;
use App\Promotion\Checker\Rule\B2bProgramPromotionRuleChecker;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_promotion_coupon")
 */
class PromotionCoupon extends BasePromotionCoupon
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="personalCoupon")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Customer $customer = null;

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function isB2bProgramPromotion(): bool
    {
        if (!$this->getPromotion()) {
            return false;
        }

        return $this->getPromotion()->getRules()->filter(
            static fn ($object) => $object->getType() === B2bProgramPromotionRuleChecker::TYPE
        )->count() > 0;
    }
}
