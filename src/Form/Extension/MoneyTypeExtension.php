<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MoneyTypeExtension extends AbstractTypeExtension
{
    private int $defaultDivisor;

    public function __construct(int $defaultDivisor)
    {
        $this->defaultDivisor = $defaultDivisor;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            MoneyType::class,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'divisor' => $this->defaultDivisor,
                'scale' => 3,
            ])
        ;
    }
}
