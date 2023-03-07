<?php

declare(strict_types=1);

namespace App\Erp\Adapters;

use App\Entity\Product\ProductVariant;
use App\Model\Erp\ErpItemModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductVariantAdapter
{
    private AdapterProvider $productProvider;
    private LoggerInterface $erpImportLogger;
    private ValidatorInterface $validator;
    /**
     * For debug only : set to true to force productVariant update
     */
    public const FORCE_COUPLED = false;

    public function __construct(
        AdapterProvider $productProvider,
        LoggerInterface $erpImportLogger,
        ValidatorInterface $validator
    ) {
        $this->productProvider = $productProvider;
        $this->erpImportLogger = $erpImportLogger;
        $this->validator = $validator;
    }

    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): ProductVariant
    {
        foreach ($this->productProvider->getProductVariantAdapters() as $productVariantAdapter) {
            $productVariantAdapter->adaptProductVariant($productVariant, $erpItem);
        }

        // verify only for activated variants.
        if ($productVariant->isEnabled() && null !== $productVariant->getProduct()) {
            $validation = $this->validator->validate($productVariant, null, ['sylius', 'erp_import']);
            /** @var ConstraintViolationInterface $error */
            foreach ($validation as $error) {
                $this->erpImportLogger->warning(sprintf("[PRODUCT-VARIANT] Validation error: variant has been disabled (internalId=%s, code=%s): %s", $erpItem->getId() ?? '?', $erpItem->getCode(), $error->getMessage()));
                $productVariant->disable();
            }
        }

        return $productVariant;
    }
}
