<?php

declare(strict_types=1);

namespace App\Erp\Adapters;

use App\Entity\Product\Product;
use App\Erp\Adapters\Product\ProductAdapterInterface;
use App\Model\Erp\ErpItemModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductAdapter
{
    private AdapterProvider $productProvider;
    /**
     * For debug only : set to true to force product update
     */
    public const FORCE_COUPLED = false;
    private ValidatorInterface $validator;
    private LoggerInterface $erpImportLogger;

    public function __construct(
        AdapterProvider $productProvider,
        ValidatorInterface $validator,
        LoggerInterface $erpImportLogger
    ) {
        $this->productProvider = $productProvider;
        $this->validator = $validator;
        $this->erpImportLogger = $erpImportLogger;
    }

    public function adaptProduct(Product $product, ErpItemModel $erpItem): Product
    {
        foreach ($this->productProvider->getProductAdapters() as $productAdapter) {
            /** @var ProductAdapterInterface $productAdapter */
            $productAdapter->adaptProduct($product, $erpItem);
        }

        // verify only for activated variants.
        if ($product->isEnabled()) {
            $validation = $this->validator->validate($product, null, ['sylius', 'erp_import']);
            /** @var ConstraintViolationInterface $error */
            foreach ($validation as $error) {
                $this->erpImportLogger->warning(sprintf("[PRODUCT] Validation error: product has been disabled (internalId=%s, code=%s): %s", $erpItem->getId() ?? '?', $erpItem->getCode(), $error->getMessage()));
                $product->disable();
            }
        }

        return $product;
    }
}
