<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Card;

use App\Dto\PageLinker\PageLinkerDto;
use App\Provider\CMS\Link\PageLinkerProvider;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PageCardType extends AbstractType
{
    private PageLinkerProvider $linkerProvider;

    public function __construct(PageLinkerProvider $linkerProvider)
    {
        $this->linkerProvider = $linkerProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', WysiwygType::class, [
            'label' => 'app.form.page_card.content',
            'constraints' => [
                new Assert\NotBlank([]),
            ],
        ]);

        $pageLinkers = $this->linkerProvider->getPageLinkers();
        $builder->add('code', ChoiceType::class, [
            'choices' => array_map(fn (PageLinkerDto $linkerModel): string => $linkerModel->getCode(), $pageLinkers),
            'choice_label' => fn (string $linkerCode): string => $this->linkerProvider->getPageLinkerTitle($pageLinkers, $linkerCode),
            'label' => 'app.form.page_card.code',
            'constraints' => [
                new Assert\NotBlank([]),
            ],
        ]);

        $builder->add('label', TextType::class, [
            'label' => 'app.form.page_card.label',
            'constraints' => [
                new Assert\NotBlank([]),
            ],
        ]);

        $builder->add('hint', WysiwygType::class, [
            'required' => false,
            'label' => 'app.form.page_card.hint',
        ]);
    }
}
