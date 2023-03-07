<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Sylius\Bundle\AddressingBundle\Form\Type\CountryType;
use Sylius\Bundle\AddressingBundle\Form\Type\ProvinceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

final class CountryTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('provinces', CollectionType::class, [ // Restrict deletion of provinces
                'entry_type' => ProvinceType::class,
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'button_add_label' => 'sylius.form.country.add_province',
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CountryType::class];
    }
}
