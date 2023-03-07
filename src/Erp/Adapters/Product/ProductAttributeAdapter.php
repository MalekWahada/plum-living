<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Entity\Product\ProductAttribute;
use App\Model\Erp\ErpCustomField;
use App\Model\Erp\ErpItemModel;
use App\Provider\Product\ProductAttributeProvider;
use App\Provider\Product\ProductAttributeValueProvider;
use Doctrine\ORM\EntityManagerInterface;
use NetSuite\Classes\CustomFieldRef;
use NetSuite\Classes\SelectCustomFieldRef;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;

class ProductAttributeAdapter implements ProductAdapterInterface
{
    private const DIMENSION_REGEX = '/(\d+)\s*-\s*(\S*)/x';

    private ProductAttributeProvider $productAttributeProvider;
    private ProductAttributeValueProvider $attributeValueProvider;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(ProductAttributeProvider $productAttributeProvider, ProductAttributeValueProvider $attributeValueProvider, EntityManagerInterface $entityManager, LoggerInterface $erpImportLogger)
    {
        $this->productAttributeProvider = $productAttributeProvider;
        $this->attributeValueProvider = $attributeValueProvider;
        $this->entityManager = $entityManager;
        $this->logger = $erpImportLogger;
    }

    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        /**
         * No custom field: no need to adapt !
         */
        if (null === $erpItem->getCustomFields()) {
            return;
        }

        /**
         * For each custom fields in ERP
         */
        foreach ($erpItem->getCustomFields() as $customField) {
            if (!isset($customField->scriptId)) {
                continue;
            }

            $this->adaptDimensionAttribute($erpItem, $customField, $product);
        }
    }

    private function adaptDimensionAttribute(ErpItemModel $erpItem, CustomFieldRef $customField, ProductInterface $product): void
    {
        // Check type of custom field
        if (!$customField instanceof SelectCustomFieldRef || $customField->scriptId !== ErpCustomField::DIMENSION) {
            return;
        }

        // Extract dimension code and value
        preg_match(self::DIMENSION_REGEX, $customField->value->name, $dimensionMatches, PREG_UNMATCHED_AS_NULL);
        $attributeChoiceKey = $dimensionMatches[1];
        $attributeChoiceName = $dimensionMatches[2] ?? $attributeChoiceKey;

        if (empty($attributeChoiceKey) || empty($attributeChoiceName)) {
            $this->logger->warning(sprintf('[PRODUCT][ATTRIBUTE][DIMENSION] Invalid dimension "%s" (internalId=%s, code=%s)', $customField->value->name, $erpItem->getId() ?? '?', $erpItem->getCode()));
            return;
        }

        try {
            // Attribute will be created if not exists
            $attribute = $this->productAttributeProvider->provideAttribute(ProductAttribute::DIMENSION_ATTRIBUTE_CODE);
            $attributeChoiceKey = substr($attribute->getCode(), 0, 3) . '_' . $attributeChoiceKey; // Key must be a string, or it will be replaced with an uuid in backoffice form
            $this->productAttributeProvider->createAttributeChoiceIfNotExist($attribute, $attributeChoiceKey, $attributeChoiceName);

            $this->entityManager->persist($attribute);

            // Attribute values will be created for all locales if not exists
            $attributeValues = $this->attributeValueProvider->provideAttributeValues($product, $attribute);
            foreach ($attributeValues as $attributeValue) {
                $attributeValue->setValue([$attributeChoiceKey]); // Value is coupled and always reset.
                $this->entityManager->persist($attributeValue);
            }
        } catch (\Exception $e) {
            $this->logger->critical(sprintf('[PRODUCT][ATTRIBUTE][DIMENSION] Error while processing attribute dimension "%s" (internalId=%s, code=%s)', $customField->value->name, $erpItem->getId() ?? '?', $erpItem->getCode()), ['exception' => $e]);
        }
    }
}
