<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Entity\Product\ProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValueChoiceHiddenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $event->setData($options['productOptionValue']);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', ProductOptionValueInterface::class);
        $resolver
            ->setDefined('productOptionValue')
            ->setAllowedTypes('productOptionValue', ProductOptionValue::class);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
