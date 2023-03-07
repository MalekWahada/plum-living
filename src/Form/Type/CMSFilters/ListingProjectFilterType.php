<?php

declare(strict_types=1);

namespace App\Form\Type\CMSFilters;

use App\Form\Type\ColorType;
use App\Model\CMSFilter\ListingProjectFilterModel;
use App\Provider\CMS\ProjectPiece\ProjectPieceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingProjectFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('piece', ChoiceType::class, [
            'required' => false,
            'placeholder' => 'app.ui.listing.projects.pieces_filter.type_all_pieces',
            'choices' => ProjectPieceProvider::ALLOWED_PIECES_TYPES,
            'label' => false,
            'choice_label' => fn (string $value): string => 'app.ui.listing.projects.pieces_filter.type_' . $value,
        ]);
        $builder->add('color', ColorType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', ListingProjectFilterModel::class);
    }
}
