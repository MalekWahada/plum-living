<?php

declare(strict_types=1);

namespace App\Provider\Product;

use App\Entity\Product\Product;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxonomy\Taxon;
use App\Provider\Tunnel\Shopping\CombinationProvider;
use App\Repository\Product\ProductOptionValueRepository;
use App\Repository\Taxon\TaxonRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class ProductOptionValueProvider
{
    private const OPTION_FACADE_CODE_KEY = 'facadeTypeCode';
    private const OPTION_DESIGN_CODE_KEY = 'designCode';
    private const OPTION_FINISH_CODE_KEY = 'finishCode';
    private const OPTION_COLOR_CODE_KEY = 'colorCode';

    private const OPTION_KEYS = [
        self::OPTION_FACADE_CODE_KEY,
        self::OPTION_DESIGN_CODE_KEY,
        self::OPTION_FINISH_CODE_KEY,
        self::OPTION_COLOR_CODE_KEY,
    ];

    const EMPTY_OPTION_KEYS = [
        ProductOptionValueProvider::OPTION_FACADE_CODE_KEY => null,
        ProductOptionValueProvider::OPTION_DESIGN_CODE_KEY => null,
        ProductOptionValueProvider::OPTION_FINISH_CODE_KEY => null,
        ProductOptionValueProvider::OPTION_COLOR_CODE_KEY => null,
    ];

    private CombinationProvider $combinationProvider;

    private ProductOptionValueRepository $productOptionValueRepository;

    private TaxonRepository $taxonRepository;

    private CacheManager $cacheManager;

    public function __construct(
        combinationProvider $combinationProvider,
        ProductOptionValueRepository $productOptionValueRepository,
        TaxonRepository $taxonRepository,
        CacheManager $cacheManager
    ) {
        $this->combinationProvider = $combinationProvider;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->taxonRepository = $taxonRepository;
        $this->cacheManager = $cacheManager;
    }

    public function findFacadeType(string $facadeCode): ?Taxon
    {
        return $this->taxonRepository->findTaxonFacadeType($facadeCode);
    }

    /**
     * Get a list of designs with images based on the combination between design and facade type
     *
     * The returned result is an array which has the following structure:
     * [
     *     0 => [
     *         'image' => string,
     *         'design' => ProductOptionValue::class
     *     ]
     * ]
     *
     * @param Taxon $facadeType
     * @return array|null
     */
    public function getDesignsByCombinationImage(Taxon $facadeType): ?array
    {
        $designs = [];
        $designsList = $this->productOptionValueRepository->getOptionValuesByVariants('design', $facadeType);

        foreach ($designsList as $key => $design) {
            if ($design->getValue() === ProductOptionValue::DESIGN_UNIQUE_CODE) {
                continue;
            }
            $combination = $this->combinationProvider->findCombination($facadeType, $design);
            $designs[$key]['image'] = (null !== $combination) ? $combination->getImage(): null;
            $designs[$key]['design'] = $design;
        }

        return $designs;
    }

    public function getFinishes(Taxon $facadeType, ProductOptionValue $design): ?array
    {
        return $this->productOptionValueRepository->getOptionValuesByVariants('finish', $facadeType, $design);
    }

    public function getColors(
        Taxon $facadeType,
        ProductOptionValue $design,
        ProductOptionValue $finish
    ): ?array {
        return $this->productOptionValueRepository->getOptionValuesByVariants('color', $facadeType, $design, $finish);
    }

    /**
     * Get an array of product variants by a succession of multiple options
     *
     * The returned result is an ?array which has the following structure:
     * [
     *     'facadeType' => Taxon::class,
     *     'designs' => [
     *          0 => [
     *              'image' => string,
     *              'design' => ProductOptionValue::class
     *          ]
     *      ], // if finishCode is not provided
     *     'design' => ProductOptionValue::class,
     *     'finishes' => [
     *          0 => ProductOptionValue::class
     *      ],
     *     'finish' => ProductOptionValue::class
     *     'allAvailableColors' => [
     *          0 => ProductOptionValue::class
     *      ],
     *     'colors' => [
     *          0 => ProductOptionValue::class
     *      ],
     *     'combination' => Combination::Class | null
     * ]
     *
     * @param string $facadeTypeCode
     * @param string|null $designCode
     * @param string|null $finishCode
     * @param string|null $colorCode
     * @return array|null
     */
    public function getByVariantsSuccession(
        string $facadeTypeCode,
        ?string $designCode,
        ?string $finishCode,
        ?string $colorCode
    ): ?array {
        $facadeType = $this->taxonRepository->findTaxonFacadeType($facadeTypeCode);

        if (null === $facadeType) {
            return null;
        }

        $options = [];

        $options['designCombinations'] = $this->getDesignsByCombinationImage($facadeType);

        if (null !== $designCode) {
            $design = $this->productOptionValueRepository->findOneByCodeAndOptionCode($designCode, 'design');

            if (null === $design) {
                return null;
            }

            $options['design'] = $design;
            $options['finishes'] = $this->getFinishes($facadeType, $design);
            $options['allAvailableColors'] = array_filter(
                $this->productOptionValueRepository->getEnabledColors(),
                static fn (ProductOptionValue $optionValue): bool => !in_array($optionValue->getCode(), ProductOptionValue::HIDDEN_COLORS, true)
            );//remove the hidden colors from the allAvailableColors list

            if (null !== $finishCode) {
                $finish = $this->productOptionValueRepository->findOneByCodeAndOptionCode($finishCode, 'finish');

                if (null === $finish) {
                    return null;
                }

                $options['finish'] = $finish;
                $options['colors'] = $this->getColors($facadeType, $design, $finish);

                // in case of selected finish is chene natural or noyer natural,
                // than the selected color is natural color
                if (ProductOptionValue::FINISH_OAK_NATURAL_CODE === $finish->getCode()
                    || ProductOptionValue::FINISH_WALNUT_NATURAL_CODE === $finish->getCode()
                ) {
                    $colorCode = ProductOptionValue::COLOR_NATURAL_CODE;
                }
            }

            if (null !== $colorCode) {
                $color = $this->productOptionValueRepository->findOneByCodeAndOptionCode($colorCode, 'color');

                if (null === $color) {
                    return null;
                }

                $options['color'] = $color;
            }
        }

        $options['facadeType'] = $facadeType;
        $options['combination'] = $this->combinationProvider->findCombination(
            $facadeType,
            $options['design'] ?? null,
            $options['finish'] ?? null,
            $options['color'] ?? null
        );

        return $options;
    }

    /**
     * Reduce ProductOptionValue objects to a minimal array with the following format needed front side :
     * [
     *      'value' => int, // id of the ProductOptionValue object
     *      'text' => string, // name of the ProductOptionValue object
     * ]
     * @param array $optionsValues
     * @return array
     */
    public function reduceOptionsValuesToArray(array $optionsValues): array
    {
        $reducedOptions = [];

        foreach ($optionsValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                continue;
            }

            $options = [
                'value' => $optionValue->getCode(),
                'text' => $optionValue->getValue(),
            ];

            if (!$optionValue->getImagesByType('default')->isEmpty()) {
                $options['thumbnail'] = $this->cacheManager->getBrowserPath(
                    $optionValue->getImagesByType('default')->first()->getPath(),
                    'app_common_form_chip_thumbnail'
                );
            }

            $reducedOptions[] = $options;
        }

        return $reducedOptions;
    }

    public function setOptionValuesCodesFromProduct(Product $product, array $options): array
    {
        $selectedOptionsValues = [];
        $optionKeyExist = true;

        foreach (self::OPTION_KEYS as $optionKey) {
            if (isset($options[$optionKey])) {
                $selectedOptionsValues[$optionKey] = $options[$optionKey];
            } else {
                $selectedOptionsValues[$optionKey] = null;
                $optionKeyExist = false;
            }
        }

        if (false === $optionKeyExist && $product->hasVariants()) {
            /** @var ProductVariant $variant */
            $variant = $product->getVariants()->first();

            $selectedOptionsValues[self::OPTION_FACADE_CODE_KEY] = $product->getMainTaxon()->getCode();
            $selectedOptionsValues[self::OPTION_DESIGN_CODE_KEY] = $variant->getOptionValueCode(ProductOption::PRODUCT_OPTION_DESIGN);
            $selectedOptionsValues[self::OPTION_FINISH_CODE_KEY] = $variant->getOptionValueCode(ProductOption::PRODUCT_OPTION_FINISH);
            $selectedOptionsValues[self::OPTION_COLOR_CODE_KEY] = $variant->getOptionValueCode(ProductOption::PRODUCT_OPTION_COLOR);
        }

        return $selectedOptionsValues;
    }
}
