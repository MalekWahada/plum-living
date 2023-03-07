<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Entity\Taxonomy\Taxon;
use App\Erp\Adapters\ProductAdapter;
use App\Exception\Erp\ErpException;
use App\Model\Erp\ErpCustomField;
use App\Model\Erp\ErpItemModel;
use NetSuite\Classes\CustomFieldRef;
use NetSuite\Classes\ListOrRecordRef;
use NetSuite\Classes\MultiSelectCustomFieldRef;
use NetSuite\Classes\SelectCustomFieldRef;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ProductCategoryAdapter implements ProductAdapterInterface
{
    private TaxonRepository $taxonRepository;
    private LoggerInterface $erpImportLogger;
    private array $assoc;
    private array $assocMain;
    private array $assocMainMultiple;
    private array $assocMultiple;
    private Product $product;
    private ErpItemModel $erpItem;
    private FactoryInterface $productTaxonFactory;

    public function __construct(
        TaxonRepository $taxonRepository,
        LoggerInterface $erpImportLogger,
        FactoryInterface $productTaxonFactory
    ) {
        $this->taxonRepository = $taxonRepository;
        $this->erpImportLogger = $erpImportLogger;
        $this->productTaxonFactory = $productTaxonFactory;

        /**
         * IMPORTANT: ALL ASSOCIATION STRINGS BELLOW MUST BE UPPERCASE
         * Associate directly ERP field (category, sub-category or family) to Sylius Taxon.
         * Note: first taxon will be the mainTaxon
         * Note: all taxon in array will be secondary taxon too (productTaxon)
         */
        $this->assocMain = [
            'METOD FRONT' => Taxon::TAXON_FACADE_METOD,
            'PAX FRONT' => Taxon::TAXON_FACADE_PAX,
            'ACCESSORIES' => Taxon::TAXON_ACCESSORY_CODE,
            'PAINT' => Taxon::TAXON_PAINT_CODE,
            'PACKAGING' => Taxon::TAXON_SAMPLE_CODE,
        ];
        $this->assoc = [
            'ACCESSORIES' => [Taxon::TAXON_ACCESSORY_CODE],
            'METOD FRONT' => [Taxon::TAXON_FACADE_METOD, Taxon::TAXON_FACADE_CODE],
            'PAX FRONT' => [Taxon::TAXON_FACADE_PAX, Taxon::TAXON_FACADE_CODE],
            'PAINT' => [Taxon::TAXON_PAINT_CODE],
            'PACKAGING' => [Taxon::TAXON_SAMPLE_CODE],
            'SAMPLES' => [Taxon::TAXON_SAMPLE_CODE],
        ];

        /**
         * Associate with multiple criteria:
         * eg: IF FRONT DOOR and TAXON_FACADE_PAX set TAXON_FACADE_PAX_DOOR_CODE
         */
        $this->assocMainMultiple = [
            [
                'require' => ['ACCESSORIES', 'TAP'],
                'set' => Taxon::TAXON_TAP_CODE,
            ],
        ];
        $this->assocMultiple = [
            // METHOD
            [
                'require' => ['METOD FRONT', 'FRONT DOOR (LEFT)'],
                'set' => Taxon::TAXON_FACADE_METOD_DOOR_CODE,
            ],
            [
                'require' => ['METOD FRONT', 'FRONT DOOR (RIGHT / UNSPEC.)'],
                'set' => Taxon::TAXON_FACADE_METOD_DOOR_CODE,
            ],
            [
                'require' => ['METOD FRONT', 'CORNER DOOR'],
                'set' => Taxon::TAXON_FACADE_METOD_DOOR_CODE,
            ],
            [
                'require' => ['METOD FRONT', 'DISHWASHER FRONT'],
                'set' => Taxon::TAXON_FACADE_METOD_DOOR_CODE,
            ],
            [
                'require' => ['METOD FRONT', 'DRAWER FRONT'],
                'set' => Taxon::TAXON_FACADE_METOD_DRAWER_CODE,
            ],
            [
                'require' => ['METOD FRONT', 'PLINTH'],
                'set' => Taxon::TAXON_FACADE_METOD_BASEBOARD_CODE,
            ],
            [
                'require' => ['METOD FRONT', 'SIDE 16mm'],
                'set' => Taxon::TAXON_FACADE_METOD_PANEL_CODE
            ],

            // PAX
            [
                'require' => ['PAX FRONT', 'FRONT DOOR (LEFT)'],
                'set' => Taxon::TAXON_FACADE_PAX_DOOR_CODE,
            ],
            [
                'require' => ['PAX FRONT', 'FRONT DOOR (RIGHT / UNSPEC.)'],
                'set' => Taxon::TAXON_FACADE_PAX_DOOR_CODE,
            ],
            [
                'require' => ['PAX FRONT', 'CORNER DOOR'],
                'set' => Taxon::TAXON_FACADE_PAX_DOOR_CODE,
            ],
            [
                'require' => ['PAX FRONT', 'SIDE 16mm'],
                'set' => Taxon::TAXON_FACADE_PAX_PANEL_CODE,
            ],

            // TAP
            [
                'require' => ['ACCESSORIES', 'TAP'],
                'set' => Taxon::TAXON_TAP_CODE,
            ],
        ];
    }

    public static function getDefaultPriority(): int
    {
        return 10;
    }

    /**
     * Categories / taxons are UNCOUPLED to the ERP
     * @param Product $product
     * @param ErpItemModel $erpItem
     * @throws ErpException
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        $this->product = $product;
        $this->erpItem = $erpItem;

        /**
         * Product 'découplé' == created but not updated
         */
        if (false === ProductAdapter::FORCE_COUPLED && null !== $this->product->getId()) { /** @phpstan-ignore-line */
            return;
        }

        /**
         * No custom field: no need to adapt !
         */
        if (null === $this->erpItem->getCustomFields()) {
            return;
        }

        $erpCustomFields = $this->transformErpItemsCustomFields($this->erpItem->getCustomFields());
        $this->adaptOptions($erpCustomFields);
    }

    /**
     * @param array $erpCustomFields
     * @throws ErpException
     */
    private function adaptOptions(array $erpCustomFields): void
    {
        $mainTaxon = null;
        $taxons = [];

        /**
         * 1. Search for direct association
         */
        foreach ($erpCustomFields as $customValue) {
            // Set main taxon
            if (isset($this->assocMain[$customValue])) {
                $mainTaxon = $this->assocMain[$customValue];
            }
            // Set product taxons
            if (isset($this->assoc[$customValue])) {
                $taxons = $this->assoc[$customValue];
            }
        }

        /**
         * 2. Override main taxon if found in multiple association
         */
        foreach ($this->assocMainMultiple as $assocMain) {
            $intersect = array_intersect($assocMain['require'], $erpCustomFields);  // Search if 'require' array is contained in fields
            if ($assocMain['require'] === $intersect) {
                $mainTaxon = $assocMain['set'];
            }
        }

        /**
         * 3. Add secondary taxons from multiple association
         */
        foreach ($this->assocMultiple as $assoc) {
            $intersect = array_intersect($assoc['require'], $erpCustomFields); // Search if 'require' array is contained in fields
            if ($assoc['require'] === $intersect) {
                $taxons[] = $assoc['set'];
            }
        }

        /**
         * 4. Override for handles
         */
        if ($this->isHandle($erpCustomFields)) {
            $mainTaxon = Taxon::TAXON_ACCESSORY_HANDLE_CODE;
            $taxons = [Taxon::TAXON_ACCESSORY_HANDLE_CODE, Taxon::TAXON_ACCESSORY_CODE];
        }

        if (null !== $mainTaxon) {
            $this->setMainTaxon($mainTaxon);
        }
        if (!empty($taxons)) {
            foreach ($taxons as $taxon) {
                $this->addTaxon($taxon);
            }
        }
    }

    private function isHandle(array $erpCustomFields): bool
    {
        return in_array('ACCESSORIES', $erpCustomFields, true) &&
            (
                in_array(
                    ErpCustomField::HANDLE_DIMENSION,
                    array_column($this->erpItem->getCustomFields(), 'scriptId'),
                    true
                ) ||
                in_array(
                    ErpCustomField::HANDLE_FINISH,
                    array_column($this->erpItem->getCustomFields(), 'scriptId'),
                    true
                ) ||
                in_array(
                    ErpCustomField::HANDLE_DESIGN,
                    array_column($this->erpItem->getCustomFields(), 'scriptId'),
                    true
                )
            );
    }

    /**
     * @param string $taxonCode
     * @throws ErpException
     */
    private function setMainTaxon(string $taxonCode): void
    {
        $taxon = $this->getTaxon($taxonCode);
        $this->product->setMainTaxon($taxon);
    }

    /**
     * Add a non main taxon
     * @param string $taxonCode
     * @throws ErpException
     */
    private function addTaxon(string $taxonCode): void
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->getTaxon($taxonCode);

        if (!$this->product->hasTaxon($taxon)) {
            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setProduct($this->product);
            $productTaxon->setTaxon($taxon);
            $this->product->addProductTaxon($productTaxon);
        }
    }

    /**
     * Filter and transform custom fields
     * Returns CATEGORY, SUB-CATEGORY and FAMILY fields only
     * @param array $erpCustomFields
     * @return array
     */
    private function transformErpItemsCustomFields(array $erpCustomFields) : array
    {
        // Remove useless fields or invalid type (not select / multiselect).
        $erpCustomFields = array_filter(
            $erpCustomFields,
            static function ($field) {
                /** @var CustomFieldRef $field */
                return (isset($field->scriptId) && ($field->scriptId === ErpCustomField::PRODUCT_CATEGORY || $field->scriptId === ErpCustomField::PRODUCT_SUB_CATEGORY || $field->scriptId === ErpCustomField::FAMILY))
                    && ($field instanceof SelectCustomFieldRef || $field instanceof MultiSelectCustomFieldRef);
            }
        );

        // Extract scriptName => value
        $fields = [];
        /** @var SelectCustomFieldRef|MultiSelectCustomFieldRef $field */
        foreach ($erpCustomFields as $field) {
            /** @var ListOrRecordRef $fieldValue */
            $fieldValue = $field->value;
            $fields[$field->scriptId] = strtoupper(trim($fieldValue->name));
        }

        return $fields;
    }

    /**
     * @param string $taxonCode
     * @return TaxonInterface
     * @throws ErpException
     */
    private function getTaxon(string $taxonCode): TaxonInterface
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        if (null === $taxon) {
            $this->erpImportLogger->error('ERROR ' . self::class . ' : TAXON  ' . $taxonCode . ' Not present' . __FILE__ . ' line ' . __LINE__ . PHP_EOL);
            throw new ErpException('TAXON  ' . $taxonCode . ' Not present');
        }
        return $taxon;
    }
}
