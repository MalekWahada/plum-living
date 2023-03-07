<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\ColorType;
use App\Form\Type\Product\ProductVariantAutocompleteType;
use App\Form\Type\UiElement\Traits\ImageElement;
use App\Form\Type\UiElement\Traits\LinkElement;
use App\Repository\Product\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaZoningElementType extends AbstractType
{
    use ImageElement;
    use LinkElement;

    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('positioning', ButtonType::class, [
            'label' => 'app.form.media.zoning_image.position',
            'attr' => [
                'class' => 'slim_positioning ui green labeled icon button',
                'style' => 'margin-bottom: 15px;margin-top: 30px',
            ]
        ]);
        // pourcentage left
        $builder->add('left', TextType::class, [
            'label' => 'app.form.media.zoning_image.left',
            'required' => true,
        ]);
        // pourcentage top
        $builder->add('top', TextType::class, [
            'label' => 'app.form.media.zoning_image.top',
            'required' => true,
        ]);

        // product
        $builder->add('product', ProductVariantAutocompleteType::class, [
            'label' => false,
        ]);

        $builder->add('manual', TextType::class, [
            'label' => 'app.form.media.zoning_image.manual',
            'required' => false,
        ]);
        $this->addImage($builder, 'image', [
            'required' => false,
        ]);

        $this->addLink($builder);

        $builder->add('color', ColorType::class, [
            'label' => 'app.form.circle_color',
        ]);
    }
}
