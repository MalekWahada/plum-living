<?php

declare(strict_types=1);

namespace App\Form\Type\Filter;

use App\Entity\Taxonomy\Taxon;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductTaxonomyFilterType extends AbstractType
{
    private RepositoryInterface $taxonRepository;

    public function __construct(RepositoryInterface $taxonRepository)
    {
        $this->taxonRepository = $taxonRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('taxonomy', ChoiceType::class, [
            'label' => false,
            'required' => false,
            'placeholder' => 'sylius.ui.all',
            'choices' => array_filter(
                $this->taxonRepository->findAll(),
                fn (Taxon $taxon) => null !== $taxon->getParent()
            ),
            'choice_label' => fn (Taxon $taxon): string => $taxon->getName(),
            'choice_value' => fn (?Taxon $taxon): string => null !== $taxon ? $taxon->getCode() : '',
        ]);
    }
}
