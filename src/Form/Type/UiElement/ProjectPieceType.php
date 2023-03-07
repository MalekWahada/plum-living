<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Provider\CMS\ProjectPiece\ProjectPieceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectPieceType extends AbstractType
{
    private ProjectPieceProvider $projectPieceProvider;

    public function __construct(ProjectPieceProvider $projectPieceProvider)
    {
        $this->projectPieceProvider = $projectPieceProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('piece', ChoiceType::class, [
            'label' => 'app.form.project_piece.label',
            'choices' => $this->projectPieceProvider->getPiecesTypeChoices(),
            'choice_label' => fn (string $label): string => 'app.ui.cms.project_piece.type_' . $label,
        ]);
    }
}
