<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use App\Entity\Product\DeliveryDateCalculationConfig;
use PhpSpec\Wrapper\Subject\Expectation\Positive;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

class DeliveryDateCalculationConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('minDateDelivery', DateType::class, [
                'label' => 'app.ui.delivery_date_calculation_config.min_date',
                'widget' => 'single_text',
                'required' => false,
                'years' => range((int) date('Y') - 1, (int) date('Y') + 5)
            ])
            ->add('maxDateDelivery', DateType::class, [
                'label' => 'app.ui.delivery_date_calculation_config.max_date',
                'widget' => 'single_text',
                'required' => false,
                'years' => range((int) date('Y') - 1, (int) date('Y') + 5),
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'app.ui.delivery_date_calculation_config.duration',
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliveryDateCalculationConfig::class,
        ]);
    }
}
