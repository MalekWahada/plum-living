<?php

declare(strict_types=1);

namespace App\Form\Type\CMSFilters;

use App\Model\CMSFilter\ListingInspirationFilterModel;
use App\Provider\CMS\Chip\ChipProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingInspirationFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'chip',
            ChoiceType::class,
            [
                'choices' => ChipProvider::CHIPS_TYPES,
                'required' => false,
                'label' => false,
                'placeholder' => 'app.ui.listing.inspirations.type_all_types',
                'choice_label' => fn (string $value): string => 'app.ui.listing.inspirations.type_' . $value,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', ListingInspirationFilterModel::class);
    }
}
