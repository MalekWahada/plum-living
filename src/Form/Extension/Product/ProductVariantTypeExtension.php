<?php

declare(strict_types=1);

namespace App\Form\Extension\Product;

use App\Entity\Product\ProductVariant;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductVariantTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deliveryCalculationMode', ChoiceType::class, [
                'label' => 'app.ui.delivery_date_calculation_mode',
                'choices' => [
                    ProductVariant::DELIVERY_DATE_CALCULATION_MODE_DYNAMIC,
                    ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER,
                    ProductVariant::DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD
                ],
                'choice_label' => function ($choice) {
                    return 'app.ui.delivery_date_calculation_config.modes.' . $choice;
                },
            ])
            ->add('minDayDelivery', IntegerType::class, [
                'label' => 'app.form.variant.min_day_delivery',
            ])
            ->add('maxDayDelivery', IntegerType::class, [
                'label' => 'app.form.variant.max_day_delivery',
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductVariantType::class];
    }
}
