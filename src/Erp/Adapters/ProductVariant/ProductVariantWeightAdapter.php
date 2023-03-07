<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Converter\WeightUnitConverter;
use App\Entity\Product\ProductVariant;
use App\Model\Erp\ErpItemModel;
use NetSuite\Classes\ItemWeightUnit;
use NetSuite\Classes\RecordRef;
use Psr\Log\LoggerInterface;

class ProductVariantWeightAdapter implements ProductVariantAdapterInterface
{
    private WeightUnitConverter $converter;
    private LoggerInterface $logger;

    public function __construct(WeightUnitConverter $converter, LoggerInterface $logger)
    {
        $this->converter = $converter;
        $this->logger = $logger;
    }

    /**
     * Weight is COUPLED with the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        $item = $erpItem->getItem();
        if (null === $item) {
            return;
        }

        /** @noinspection MissingIssetImplementationInspection */
        if (isset($item->weight, $item->weightUnit)) {
            $unit = null;

            // Transform unit for kitItems
            if ($item->weightUnit instanceof RecordRef) {
                $unit = '_' . $item->weightUnit->name;
            } else {
                $unit = $item->weightUnit;
            }

            if (empty($unit)) {
                return;
            }

            $weight = $item->weight;
            switch ($unit) {
                case ItemWeightUnit::_lb:
                    $weight = $this->converter->convertFromLb($weight);
                    break;
                case ItemWeightUnit::_oz:
                    $weight = $this->converter->convertFromOz($weight);
                    break;
                case ItemWeightUnit::_kg:
                    $weight = $this->converter->convertFromKg($weight);
                    break;
                case ItemWeightUnit::_g:
                    $weight = $this->converter->convertFromGram($weight);
                    break;
                default:
                    $this->logger->warning(sprintf('[PRODUCT-VARIANT][WEIGHT] Unknown weight unit "%s" (internalId=%s, code=%s)', $unit, $erpItem->getId() ?? '?', $erpItem->getCode()));
                    return;
            }
            $productVariant->setWeight(round($weight, 3));
        }
    }
}
