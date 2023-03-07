<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Provider\CMS\Chip\ChipProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChipCategoryChoiceType extends AbstractType
{
    private ChipProvider $chipProvider;

    public function __construct(ChipProvider $chipProvider)
    {
        $this->chipProvider = $chipProvider;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices'      => $this->chipProvider->getChips(),
            'choice_label' => fn (string $label): string => 'app.ui.cms.chip.admin.type_' . $label,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
