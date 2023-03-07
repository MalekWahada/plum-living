<?php

declare(strict_types=1);

namespace App\Form\Type\Action;

use Sylius\Bundle\PromotionBundle\Form\DataTransformer\PercentFloatToLocalizedStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class OrderPercentageOfFrontItemsConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('percentage', PercentType::class, [
                'label' => 'sylius.form.promotion_action.percentage_discount_configuration.percentage',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new Type(['type' => 'numeric', 'groups' => ['sylius']]),
                    new Range([
                        'min' => 0,
                        'max' => 1,
                        'notInRangeMessage' => 'sylius.promotion_action.percentage_discount_configuration.not_in_range',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
        ;
        $builder->get('percentage')->resetViewTransformers()->addViewTransformer(new PercentFloatToLocalizedStringTransformer());
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_action_order_percentage_of_front_items_configuration';
    }
}
