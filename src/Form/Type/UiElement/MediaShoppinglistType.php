<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\ColorBackgroundElement;
use App\Form\Type\UiElement\Traits\ImageElement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaShoppinglistType extends AbstractType
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

        // plum
        $builder->add('plum', CollectionType::class, [
            'label' => false,
            'entry_type' => MediaShoppinglistProductPlumType::class,
            'button_add_label' => 'app.form.media.shoppinglist.add_element_plum',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);

        // ikea
        $builder->add('ikea', CollectionType::class, [
            'label' => false,
            'entry_type' => MediaShoppinglistProductIkeaType::class,
            'button_add_label' => 'app.form.media.shoppinglist.add_element_ikea',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
        ]);
    }
}
