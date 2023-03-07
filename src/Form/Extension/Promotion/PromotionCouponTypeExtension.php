<?php

declare(strict_types=1);

namespace App\Form\Extension\Promotion;

use Sylius\Bundle\PromotionBundle\Form\Type\PromotionCouponType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionCouponTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['Default', 'app_promotion_coupon'],
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PromotionCouponType::class];
    }
}
