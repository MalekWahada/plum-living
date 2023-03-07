<?php

declare(strict_types=1);

namespace App\Form\Extension\UiElement;

use App\Provider\CMS\Button\ButtonProvider;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\UiElement\ButtonLinkType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ButtonLinkTypeExtension extends AbstractTypeExtension
{
    private ButtonProvider $buttonProvider;

    public function __construct(ButtonProvider $buttonProvider)
    {
        $this->buttonProvider = $buttonProvider;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ButtonLinkType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('link', TextType::class, [
            'label' => 'app.form.button_link.link'
        ]);
        $builder->add('type', ChoiceType::class, [
            'label' => 'app.form.button_link.label',
            'choices' => $this->buttonProvider->getButtonTypes(),
            'choice_label' => fn (string $label): string => 'app.ui.cms.button_link.type_' . $label,
        ]);
    }
}
