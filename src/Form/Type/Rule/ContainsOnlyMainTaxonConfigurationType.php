<?php

declare(strict_types=1);

namespace App\Form\Type\Rule;

use Sylius\Bundle\CoreBundle\Form\DataTransformer\TaxonsToCodesTransformer;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContainsOnlyMainTaxonConfigurationType extends AbstractType
{
    private TaxonsToCodesTransformer $taxonsToCodesTransformer;

    public function __construct(TaxonsToCodesTransformer $taxonsToCodesTransformer)
    {
        $this->taxonsToCodesTransformer = $taxonsToCodesTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('taxons', TaxonAutocompleteChoiceType::class, [
                'label' => 'sylius.form.promotion_rule.has_taxon.taxons',
                'multiple' => true,
                'constraints' => [
                    new NotBlank([
                        'groups' => 'sylius',
                    ]),
                ],
            ])
        ;

        $builder->get('taxons')->addModelTransformer($this->taxonsToCodesTransformer);
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_rule_contains_only_main_taxon_configuration';
    }
}
