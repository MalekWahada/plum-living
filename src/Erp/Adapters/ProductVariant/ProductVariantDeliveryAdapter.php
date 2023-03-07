<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\ProductVariantAdapter;
use App\Model\Erp\ErpItemModel;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Repository\ShippingCategoryRepositoryInterface;

class ProductVariantDeliveryAdapter implements ProductVariantAdapterInterface
{
    public const SHIPPING_CODE_FACADE = 'facade';
    public const SHIPPING_CODE_OTHER = 'not_facade';

    private ShippingCategoryRepositoryInterface $shippingCategoryRepository;
    private LoggerInterface $erpImportLogger;

    public function __construct(
        ShippingCategoryRepositoryInterface $shippingCategoryRepository,
        LoggerInterface $erpImportLogger
    ) {
        $this->shippingCategoryRepository = $shippingCategoryRepository;
        $this->erpImportLogger = $erpImportLogger;
    }

    /**
     * Delivery is UNCOUPLED to the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        /**
         * Product 'découplé' == created but not updated
         */
        if (false === ProductVariantAdapter::FORCE_COUPLED && null !== $productVariant->getId()) {  /** @phpstan-ignore-line */
            return;
        }

        /** @var Product|null $product */
        $product = $productVariant->getProduct();
        if (null === $product) {
            return;
        }
        $taxonCode = $product->getMainTaxon() ? $product->getMainTaxon()->getCode() : null;

        switch ($taxonCode) {
            case 'metod':
            case 'pax':
                $productVariant->setShippingCategory($this->shippingCategoryRepository->findOneBy(['code' => self::SHIPPING_CODE_FACADE]));
                break;
            case 'echantillon':
            case 'echantillon_facade':
            case 'echantillon_peinture':
            case 'accessoires':
            case 'accessoires_handle':
            case 'peinture':
                $productVariant->setShippingCategory($this->shippingCategoryRepository->findOneBy(['code' => self::SHIPPING_CODE_OTHER]));
                break;
            case null:
                // unknow !
                break;
            default:
                $this->erpImportLogger->error(sprintf('[PRODUCT-VARIANT][DELIVERY] Unknown taxon code "%s". Shipping category set to "%s"', $taxonCode, self::SHIPPING_CODE_OTHER));
                $productVariant->setShippingCategory($this->shippingCategoryRepository->findOneBy(['code' => self::SHIPPING_CODE_OTHER]));
        }
    }
}
