<?php

declare(strict_types=1);

namespace App\Model\UiElement;

use App\Entity\Product\ProductOptionValue;
use App\Repository\Product\ProductOptionValueRepository;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementInterface;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementTrait;

class ColorUIModel implements UiElementInterface
{
    use UiElementTrait;

    private ProductOptionValueRepository $productOptionValueRepository;

    public function __construct(ProductOptionValueRepository $productOptionValueRepository)
    {
        $this->productOptionValueRepository = $productOptionValueRepository;
    }

    public function getOptionValue(string $optionValueCode): ?ProductOptionValue
    {
        return $this->productOptionValueRepository->findOneBy([
            'code' => $optionValueCode
        ]);
    }
}
