<?php

declare(strict_types=1);

namespace App\Form\Extension\Checkout;

use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CompleteTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('notes');
    }

    public static function getExtendedTypes(): iterable
    {
        return [CompleteType::class];
    }
}
