<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Erp\ErpEntity;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\ProductVariantAdapter;
use App\Erp\Slugifier;
use App\Model\Erp\ErpCustomField;
use App\Model\Erp\ErpItemModel;
use App\Provider\Product\ProductOptionProvider;
use App\Repository\Erp\ErpEntityRepository;
use App\Repository\Product\ProductOptionValueRepository;
use Doctrine\ORM\NonUniqueResultException;
use NetSuite\Classes\CustomFieldRef;
use NetSuite\Classes\MultiSelectCustomFieldRef;
use NetSuite\Classes\SelectCustomFieldRef;
use Psr\Log\LoggerInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ProductVariantOptionValueAdapter implements ProductVariantAdapterInterface
{
    private array $configDefinition = [];

    private ProductOptionValueRepository $productOptionValueRepository;
    private ErpEntityRepository $erpEntityRepository;
    private FactoryInterface $erpEntityFactory;
    private FactoryInterface $productOptionValueFactory;
    private ProductOptionProvider $productOptionProvider;
    private LoggerInterface $erpImportLogger;
    private ErpItemModel $erpItem;

    public function __construct(
        ProductOptionValueRepository $productOptionValueRepository,
        ErpEntityRepository $erpEntityRepository,
        FactoryInterface $erpEntityFactory,
        FactoryInterface $productOptionValueFactory,
        ProductOptionProvider $productOptionProvider,
        LoggerInterface $erpImportLogger
    ) {
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->erpEntityRepository = $erpEntityRepository;
        $this->erpEntityFactory = $erpEntityFactory;
        $this->productOptionValueFactory = $productOptionValueFactory;
        $this->productOptionProvider = $productOptionProvider;
        $this->erpImportLogger = $erpImportLogger;

        // Style
        $this->configDefinition['finish'] = [
            'option_name' => 'finish',
            'erp_script_id' => ErpCustomField::OPTION_FINISH,
            'associations' => [
                'Oak Veneer Painted MDF' => ProductOptionValue::FINISH_OAK_PAINTED_CODE,
                'Matt Lacquer MDF 2 Sides' => ProductOptionValue::FINISH_LACQUER_MATT_CODE,
                'Walnut Veneer Natural MDF' => ProductOptionValue::FINISH_WALNUT_NATURAL_CODE,
                'Rift Oak Veneer Natural MDF' => ProductOptionValue::FINISH_OAK_NATURAL_CODE,
                'Melamine' => ProductOptionValue::FINISH_BRASS_CODE
            ],
            'auto_add' => false,
            'coupled' => false,
        ];

        // Design
        $this->configDefinition['design'] = [
            'option_name' => 'design',
            'erp_script_id' => ErpCustomField::OPTION_DESIGN,
            'associations' => [
                'Frame' => ProductOptionValue::DESIGN_FRAME_CODE,
                'Ushape' => ProductOptionValue::DESIGN_U_SHAPE_CODE,
                'Straight' => ProductOptionValue::DESIGN_STRAIGHT_CODE,
                'Classic cane' => ProductOptionValue::DESIGN_CLASSIC_CANE_CODE,
                'Arch cane' => ProductOptionValue::DESIGN_ARCH_CANE_CODE,
            ],
            'auto_add' => false,
            'coupled' => false, // must be decoupled for unique design
        ];

        // Color
        $this->configDefinition['color'] = [
            'option_name' => 'color',
            'erp_script_id' => ErpCustomField::OPTION_COLOR,
            'auto_add' => true,
            'coupled' => true,
        ];
    }

    /**
     * Options are UNCOUPLED with the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     * @throws NonUniqueResultException
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        $this->erpItem = $erpItem;

        if (null === $productVariant->getProduct()) {
            return;
        }

        /**
         * No custom field: no need to adapt !
         */
        if (!isset($erpItem->getItem()->customFieldList->customField)) {
            return;
        }

        /**
         * Save available option codes for the product
         */
        $availableOptionCodes = array_map(static function (ProductOptionInterface $option) {
            return $option->getCode();
        }, $productVariant->getProduct()->getOptions()->toArray());

        /**
         * For each custom fields in ERP
         */
        foreach ($erpItem->getCustomFields() as $customField) {
            // The current field is a valid option?
            if (!$config = $this->getOptionConfig($customField)) {
                continue;
            }

            // Check type of custom field. We must get a select in order to get a internalId for the selected value
            if (!$customField instanceof SelectCustomFieldRef && !$customField instanceof MultiSelectCustomFieldRef) {
                $this->erpImportLogger->debug("[PRODUCT-VARIANT][OPTION-VALUE] Invalid option type for " . $customField->scriptId . "(" . $customField->internalId . "). The value should be a Select / Multiselect.");
                continue;
            }

            // Multiselect's fields are represented as an array. We keep select only first item.
            if ($customField instanceof MultiSelectCustomFieldRef) {
                if (count($customField->value) > 0) {
                    $customField->value = $customField->value[0]; /** @phpstan-ignore-line */
                } else {
                    $this->erpImportLogger->error("[PRODUCT-VARIANT][OPTION-VALUE] Invalid option value for " . $customField->scriptId . "(" . $customField->internalId . "). Multiselect list is empty.");
                }
            }

            /**
             * Product must have the option to add the option value.
             * eg: having a coupled color on a Tap (with design_tap and finish_tap for only options) could lead to errors in BO
             */
            if (!in_array($config['option_name'], $availableOptionCodes, true)) {
                continue;
            }

            // Option value will be created if its config allows it
            $optionValue = $this->getOrCreateOptionValue($config, $customField);

            /**
             * Option is 'découplé' == created but not updated, depending on its config
             */
            if (!ProductVariantAdapter::FORCE_COUPLED && isset($config['coupled']) && false === $config['coupled'] && null !== $productVariant->getId()) { /** @phpstan-ignore-line */
                continue;
            }

            // Find and remove old option values from the same option code (color, finish, ..)
            $oldOptionsValues = $this->productOptionValueRepository->findByOptionCode($config['option_name']);

            foreach ($oldOptionsValues as $oldOptionValue) {
                $productVariant->removeOptionValue($oldOptionValue);
            }

            if ($optionValue) {
                $productVariant->addOptionValue($optionValue);
            }
        }
    }

    /**
     * Get the code of the option value if the option is addable or an association exists.
     * @param array $config
     * @param string $optionValue
     * @return string|null
     */
    private function getAddableOptionValueCode(array $config, string $optionValue): ?string
    {
        // 1. Search option name in associations and check if not empty
        if (isset($config['associations'], $config['associations'][$optionValue]) && !empty($config['associations'][$optionValue])) {
            return $config['associations'][$optionValue];
        }

        // 2. Not in static associations: slugify the name if addable
        if ($config['auto_add']) {
            return Slugifier::slugifyOptionCode($config['option_name'], $optionValue);
        }

        return null; // Cannot be added
    }

    /**
     * Find if exist a local config for erp item
     * @param CustomFieldRef $erpOption
     * @return array|null
     */
    private function getOptionConfig(CustomFieldRef $erpOption): ?array
    {
        foreach ($this->configDefinition as $config) {
            if (isset($erpOption->scriptId) && $config['erp_script_id'] === $erpOption->scriptId) {
                return $config;
            }
        }
        return null;
    }

    /**
     * Search or create if not existing the ERP entity object which keeps a reference to the erp internal id
     * @param array $config
     * @param CustomFieldRef|SelectCustomFieldRef|MultiSelectCustomFieldRef $erpOption
     * @return ErpEntity
     */
    private function findOrCreateErpEntity(array $config, CustomFieldRef $erpOption): ErpEntity
    {
        $erpId = (int)$erpOption->value->internalId;

        $erpEntity = $this->erpEntityRepository->findOneBy(['type' => $config['option_name'], 'erpId' => $erpId]);
        if (null === $erpEntity) {
            /** @var ErpEntity $erpEntity */
            $erpEntity = $this->erpEntityFactory->createNew();
            $erpEntity->setName($erpOption->value->name);
            $erpEntity->setType($config['option_name']);
            $erpEntity->setErpId($erpId);
            //persist ErpEntity
            $this->erpEntityRepository->add($erpEntity);
        }
        return $erpEntity;
    }

    /**
     * @param array $config
     * @param CustomFieldRef|SelectCustomFieldRef|MultiSelectCustomFieldRef $erpOption
     * @return ProductOptionValue|null
     * @throws NonUniqueResultException
     */
    private function getOrCreateOptionValue(array $config, CustomFieldRef $erpOption): ?ProductOptionValue
    {
        /**
         * 1. Find existing option value by erp internal id
         */
        if (null !== $optionValue = $this->productOptionValueRepository->findByByErpId($config['option_name'], (int)$erpOption->value->internalId)) {
            return $optionValue;
        }

        /**
         * 2. Retrieve option value by code or create if allowed in config
         */
        $optionValueName = trim($erpOption->value->name);

        // If null: we can't add a value for this option. Option is skipped
        if (null === $optionValueCode = $this->getAddableOptionValueCode($config, $optionValueName)) {
            $this->erpImportLogger->warning(sprintf("[PRODUCT-VARIANT][OPTION-VALUE] No association exist for the option value \"%s\" (%s) and it can't be imported (product internalId=%s, code=%s)", $optionValueName, $config['option_name'], $this->erpItem->getId(), $this->erpItem->getCode()));
            return null;
        }

        /** @var ?ProductOptionValue $optionValue */
        $optionValue = $this->productOptionValueRepository->findOneBy(['code' => $optionValueCode]);

        // We add option value if not existing
        if (null === $optionValue) {
            /** @var ProductOptionValue $optionValue */
            $optionValue = $this->productOptionValueFactory->createNew();
            $option = $this->productOptionProvider->provide($config['option_name']);
            $optionValue->setOption($option);
            $optionValue->setCode($optionValueCode);
            $optionValue->setValue($optionValueName);
        }

        $erpEntity = $this->findOrCreateErpEntity($config, $erpOption);
        $optionValue->addErpEntity($erpEntity); // Always update reference to erp internal id

        try {
            $this->productOptionValueRepository->add($optionValue);
            return $optionValue;
        } catch (\Exception $e) {
            $this->erpImportLogger->error(sprintf("[PRODUCT-VARIANT][OPTION-VALUE] Cannot import option value \"%s\" (product internalId=%s, code=%s): %s", $optionValueCode, $this->erpItem->getId(), $this->erpItem->getCode(), $e->getMessage()));
        }

        return null;
    }
}
