<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Card\BudgetElementType;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class HomeProjectBudgetType extends AbstractType
{
    use ColorBackgroundElement;
    use ImageElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // background color
        $this->addBgColor($builder);

        // template orientation (left OR right)
        $builder->add('orientation', ChoiceType::class, [
            'label' => 'app.form.media.two_hybrid_columns.orientation',
            'choices' => [
                'app.form.media.two_hybrid_columns.orientation_left' => 'left',
                'app.form.media.two_hybrid_columns.orientation_right' => 'right',
            ],
        ]);

        // base64 image
        $this->addImage($builder, 'image');

        $builder->add('cardTotalDetails', TextType::class, [
            'label' => 'app.form.card_total.total_details',
        ]);

        $builder->add('cardElementsIkea', CollectionType::class, [
            'label' => 'app.form.card_total.label_ikea',
            'entry_type' => BudgetElementType::class,
            'button_add_label' => 'app.form.card_total.add_button_label',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);

        $builder->add('cardElementsPlum', CollectionType::class, [
            'label' => 'app.form.card_total.label_plum',
            'entry_type' => BudgetElementType::class,
            'button_add_label' => 'app.form.card_total.add_button_label',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);
    }
}
