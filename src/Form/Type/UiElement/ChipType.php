<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Provider\CMS\Chip\ChipProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChipType extends AbstractType
{
    private ChipProvider $chipProvider;

    public function __construct(ChipProvider $chipProvider)
    {
        $this->chipProvider = $chipProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('chip', ChoiceType::class, [
            'label' => 'app.form.chip',
            'choices' => $this->chipProvider->getChips(),
            'choice_label' => fn (string $label): string => 'app.ui.cms.chip.admin.type_' . $label,
        ]);
    }
}
