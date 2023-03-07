<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Repository\Product\ProductOptionValueRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SelectedOptionsExtension extends AbstractExtension
{
    private ProductOptionValueRepository $productOptionValueRepository;

    public function __construct(ProductOptionValueRepository $productOptionValueRepository)
    {
        $this->productOptionValueRepository = $productOptionValueRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_valid_selected_options', [$this, 'hasValidSelectedOptions']),
            new TwigFunction('get_finishes_with_disabled_colors', [$this, 'getFinishesWithDisabledColors']),
        ];
    }

    public function hasValidSelectedOptions(?array $selectedOptions): bool
    {
        if (null === $selectedOptions) {
            return false;
        }

        foreach ($selectedOptions as $selectedOption) {
            if (null === $selectedOption) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws \JsonException
     */
    public function getFinishesWithDisabledColors(): string
    {
        $scannerFinishesWithHiddenColors = [
            ProductOptionValue::FINISH_OAK_NATURAL_CODE,
            ProductOptionValue::FINISH_WALNUT_NATURAL_CODE,
        ];
        $finishes = $this->productOptionValueRepository->findByOptionAndCodes(ProductOption::PRODUCT_OPTION_FINISH, $scannerFinishesWithHiddenColors);
        $codes = [];
        /** @var ProductOptionValue $finish */
        foreach ($finishes as $finish) {
            $codes[] = $finish->getCode();
        }
        return json_encode($codes, JSON_THROW_ON_ERROR);
    }
}
