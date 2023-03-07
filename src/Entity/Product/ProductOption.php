<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_option")
 *
 * @method ProductOptionValue[] getValues()
 */
class ProductOption extends BaseProductOption
{
    public const PRODUCT_OPTION_COLOR = 'color';
    public const PRODUCT_OPTION_FINISH = 'finish';
    public const PRODUCT_OPTION_DESIGN = 'design';
    public const PRODUCT_HANDLE_OPTION_DESIGN = 'design_handle';
    public const PRODUCT_HANDLE_OPTION_FINISH = 'finish_handle';
    public const PRODUCT_TAP_OPTION_DESIGN = 'design_tap';
    public const PRODUCT_TAP_OPTION_FINISH = 'finish_tap';

    public const FACADE_SELECTED_OPTIONS = [
        self::PRODUCT_OPTION_COLOR,
        self::PRODUCT_OPTION_FINISH,
        self::PRODUCT_OPTION_DESIGN,
    ];

    protected function createTranslation(): ProductOptionTranslationInterface
    {
        return new ProductOptionTranslation();
    }

    public function getValueByCode(string $code): ?ProductOptionValue
    {
        foreach ($this->getValues() as $productOptionValue) {
            if ($productOptionValue->getCode() === $code) {
                return $productOptionValue;
            }
        }

        return null;
    }
}
