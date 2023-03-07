<?php

declare(strict_types=1);

namespace App\Form\Extension\Channel;

use Sylius\Bundle\AddressingBundle\Form\Type\CountryChoiceType;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class ChannelTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('defaultCountry', CountryChoiceType::class, [
                'label' => 'app.form.channel.default_country',
                'required' => true,
                'placeholder' => null,
            ])
        ;
    }

    public function getExtendedType(): string
    {
        return ChannelType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChannelType::class];
    }
}
