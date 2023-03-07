<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Repository\Product\ProductOptionValueRepository;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class CsvToYamlProductsFixtureCommand extends Command
{
    private ProductOptionValueRepository $productOptionValueRepository;

    protected static $defaultName = 'app:fixtures:generate:products-from-csv';

    public function __construct(ProductOptionValueRepository $productOptionValueRepository, string $name = null)
    {
        parent::__construct($name);
        $this->productOptionValueRepository = $productOptionValueRepository;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        file_put_contents(
            'config/fixtures_suites/test_products.yaml',
            "#file generated via the command: " . self::$defaultName . PHP_EOL . $this->prepareYamlFile()
        );

        return 0;
    }

    public function prepareYamlFile(): string
    {
        $file = "fixtures/fixturesProducts.csv";
        Assert::fileExists($file, 'The file %s does not exist');
        
        $fileArray = file($file);
        \assert(is_array($fileArray));

        $csv = array_map('str_getcsv', $fileArray);
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header

        // Process each line items
        foreach ($csv as $nodeKey => $node) {
            foreach ($node as $itemKey => $item) {
                // Remove empty values
                if (empty($item)) {
                    unset($csv[$nodeKey][$itemKey]);
                    continue;
                }

                // Product_options and taxons are always arrays. Explode others items by '|'.
                $exploded = explode('|', $item);
                if ($itemKey === "channels" || $itemKey === "taxons" || $itemKey === "product_options" || sizeof($exploded) > 1) {
                    $csv[$nodeKey][$itemKey] = $exploded;
                }

                // Edit images
                if ($itemKey === "images") {
                    $images = [];
                    foreach ($exploded as $image) {
                        $images[] = [
                            'path' => '%kernel.project_dir%/' . $image,
                            'type' => 'main',
                        ];
                    }
                    $csv[$nodeKey][$itemKey] = $images;
                }

                // Generate products
                if ($itemKey === "generate_products_for_design_and_finish_suffixes" && !empty($item)) {
                    unset($csv[$nodeKey]["generate_products_for_design_and_finish_suffixes"]);
                    $designs = $this->productOptionValueRepository->findByOptionCode(ProductOption::PRODUCT_OPTION_DESIGN);
                    $finishes = $this->productOptionValueRepository->findByOptionCode(ProductOption::PRODUCT_OPTION_FINISH);
                    foreach ($this->generateProductsForDesignsAndFinishes($csv[$nodeKey], $item, $designs, $finishes) as $generatedProduct) {
                        $csv[] = $generatedProduct;
                    }
                    unset($csv[$nodeKey]); // Remove current node as new products will be generated
                    continue 2;
                }
            }
        }

        $yamlContentArray = [
            'sylius_fixtures' => [
                'suites' => [
                    'test_products' => [
                        'fixtures' => [
                            'plum_product' => [
                                'options' => [
                                    'custom' => array_values($csv),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return Yaml::dump($yamlContentArray, 9);
    }

    /**
     * @param array $item
     * @param string $suffixes
     * @param array|ProductOptionValueInterface[] $designs
     * @param array|ProductOptionValueInterface[] $finishes
     * @return array
     */
    private function generateProductsForDesignsAndFinishes(array $item, string $suffixes, array $designs, array $finishes): array
    {
        $products = [];
        foreach ($designs as $design) {
            switch ($design->getCode()) {
                case ProductOptionValue::DESIGN_STRAIGHT_CODE:
                    $designSuffix = "S";
                    break;
                case ProductOptionValue::DESIGN_U_SHAPE_CODE:
                    $designSuffix = "U";
                    break;
                case ProductOptionValue::DESIGN_FRAME_CODE:
                    $designSuffix = "F";
                    break;
                case ProductOptionValue::DESIGN_CLASSIC_CANE_CODE:
                    $designSuffix = "C";
                    break;
                default: // Don't process other finishes
                    continue 2;
            }

            // Design must be in suffixes list
            if (strpos($suffixes, $designSuffix) === false) {
                continue;
            }

            foreach ($finishes as $finish) {
                switch ($finish->getCode()) {
                    case ProductOptionValue::FINISH_LACQUER_MATT_CODE:
                        $finishSuffix = "L";
                        break;
                    case ProductOptionValue::FINISH_OAK_NATURAL_CODE:
                        $finishSuffix = "O";
                        break;
                    case ProductOptionValue::FINISH_OAK_PAINTED_CODE:
                        $finishSuffix = "P";
                        break;
                    case ProductOptionValue::FINISH_WALNUT_NATURAL_CODE:
                        $finishSuffix = "W";
                        break;
                    default: // Don't process other finishes
                        continue 2;
                }

                // Cane can only be set with Painted
                // Frame can only be set with Painted or Lacquer
                // Finish must be in suffixes list
                if (($designSuffix === "C" && $finishSuffix !== "P")
                    || ($designSuffix === "F" && $finishSuffix !== "P" && $finishSuffix !== "L")
                    || strpos($suffixes, $finishSuffix) === false) {
                    continue;
                }

                $newItem = $item;
                $newItem['code'] .= $designSuffix . '-' . $finishSuffix;
                $newItem['name'] .= ' | ' . $design->getValue() . ' | ' . $finish->getValue();
                $newItem['product_options_values'] = [
                    "design" => $design->getCode(),
                    "finish" => $finish->getCode(),
                ];
                if ($finishSuffix === 'O' || $finishSuffix === 'W') {
                    $newItem['product_options_values']['color'] = ProductOptionValue::COLOR_NATURAL_CODE;
                }
                $products[] = $newItem;
            }
        }

        return $products;
    }
}
