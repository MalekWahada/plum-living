<?php

declare(strict_types=1);

namespace App\Model\UiElement;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Provider\CMS\ProductOptionColor\ProductOptionColorProvider;
use App\Repository\Product\ProductOptionValueRepository;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementInterface;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementTrait;

class ProductOptionModel implements UiElementInterface
{
    use UiElementTrait;

    private ProductOptionValueRepository $productOptionValueRepository;
    private ProductOptionColorProvider $productOptionColorProvider;

    public function __construct(
        ProductOptionValueRepository $productOptionValueRepository,
        ProductOptionColorProvider $productOptionColorProvider
    ) {
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->productOptionColorProvider = $productOptionColorProvider;
    }

    public function getProductOptions(string $value): array
    {
        switch ($value) {
            case ProductOption::PRODUCT_OPTION_COLOR:
                return $this->productOptionColorProvider->getColorsCodes();
            case ProductOption::PRODUCT_OPTION_FINISH:
                $finishCodes = [
                    ProductOptionValue::FINISH_LACQUER_MATT_CODE,
                    ProductOptionValue::FINISH_OAK_NATURAL_CODE,
                    ProductOptionValue::FINISH_OAK_PAINTED_CODE,
                    ProductOptionValue::FINISH_WALNUT_NATURAL_CODE,
                ];
                return $this->productOptionValueRepository->findByOptionAndCodes($value, $finishCodes);
            default:
                return $this->productOptionValueRepository->findByOptionCode($value);
        }
    }
}
