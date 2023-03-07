<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\ProductVariantAdapter;
use App\Model\Erp\ErpItemModel;
use App\Repository\Product\ProductRepository;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ProductInterface;

class ProductVariantProductAdapter implements ProductVariantAdapterInterface
{
    private ProductRepository $productRepository;
    private LoggerInterface $erpImportLogger;

    public function __construct(ProductRepository $productRepository, LoggerInterface $erpImportLogger)
    {
        $this->productRepository = $productRepository;
        $this->erpImportLogger = $erpImportLogger;
    }

    // defined with high priority
    public static function getDefaultPriority(): int
    {
        return 128;
    }

    /**
     * Product of a variant is UNCOUPLED with the ERP
     * Some Paint products have a different parent than the original matrix one
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        /**
         * ProductVariant 'découplé'' == created but not updated
         */
        if (false === ProductVariantAdapter::FORCE_COUPLED && null !== $productVariant->getId()) { /** @phpstan-ignore-line */
            return;
        }

        if ($erpItem->isChild()) {
            $parentErpId = $erpItem->getParentId();
        } else { // Parent is himself
            $parentErpId = $erpItem->getId();
        }

        /** @var ProductInterface|null $productParent */
        $productParent = $this->productRepository->findOneByErpId((int)$parentErpId);

        if (null === $productParent) {
            if ($productVariant->isEnabled()) {
                $this->erpImportLogger->warning(sprintf("[PRODUCT-VARIANT][PARENT-PRODUCT] Error with variant internalId=%s. Parent internalId=%s has not been found. The product-variant has been disabled.", $erpItem->getId() ?? '?', $parentErpId ?? '?'));
            }
            $productVariant->disable();
            return;
        }

        $productVariant->setProduct($productParent);
    }
}
