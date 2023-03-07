<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Product\ProductOptionValue;
use App\Provider\CMS\ProductOptionColor\ProductOptionColorProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorType extends AbstractType
{
    private array $colors;

    public function __construct(ProductOptionColorProvider $productOptionColorProvider)
    {
        $this->colors = $productOptionColorProvider->getColorsCodes();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // we need to have only color codes because we are interested to store only a color code for a given CMS project.
        // Also the listing projects filter is dependant to that because filtering is made based on the content(long text).
        $colorsCodes = array_map(
            fn (ProductOptionValue $productOptionValue): string => $productOptionValue->getCode(),
            $this->colors
        );
        $resolver->setDefaults([
            'choices' => $colorsCodes,
            'required' => false,
            'label' => false,
            'placeholder' => 'app.ui.listing.projects.color_filter',
            'choice_label' => fn (string $code): ?string => $this->getColorValue($code),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'app_option_value_color';
    }

    private function getColorValue(string $code): ?string
    {
        /**
         * @var ProductOptionValue $color
         */
        foreach ($this->colors as $color) {
            if ($color->getCode() === $code) {
                return $color->getValue();
            }
        }
        return null;
    }
}
