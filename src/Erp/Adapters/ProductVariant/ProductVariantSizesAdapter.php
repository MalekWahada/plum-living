<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Model\Erp\ErpCustomField;
use App\Model\Erp\ErpItemModel;
use NetSuite\Classes\LongCustomFieldRef;

class ProductVariantSizesAdapter implements ProductVariantAdapterInterface
{
    /**
     * Sizes are COUPLED with the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
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
            // Skip specific fields
            if (!isset($customField->scriptId)) {
                continue;
            }

            // Check type of custom field in order to get an internalId and value
            if (!$customField instanceof LongCustomFieldRef) {
                continue;
            }

            $value = (int)($customField->value ?? 0);
            switch ($customField->scriptId) {
                case ErpCustomField::SPECS_WIDTH:
                    $productVariant->setWidth($value);
                    break;
                case ErpCustomField::SPECS_HEIGHT:
                    $productVariant->setHeight($value);
                    break;
                case ErpCustomField::SPECS_DEPTH:
                    $productVariant->setDepth($value);
                    break;
                default:
                    continue 2;
            }
        }
    }
}
