<?php

declare(strict_types=1);

namespace App\Erp;

use App\Entity\Erp\ErpEntity;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\ProductAdapter;
use App\Erp\Adapters\ProductVariantAdapter;
use App\Exception\Erp\ErpException;
use App\Model\Erp\ErpItemModel;
use App\Provider\Erp\ErpEntityProvider;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductVariantRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class NetsuiteItemTransformer
{
    private ProductRepository $productRepository;
    private ProductFactoryInterface $productFactory;
    private ProductVariantFactoryInterface $productVariantFactory;
    private ProductVariantRepository $productVariantRepository;
    private ProductAdapter $productAdapter;
    private EntityManagerInterface $entityManager;
    private ProductVariantAdapter $productVariantAdapter;
    private LoggerInterface $erpImportLogger;
    private EventDispatcherInterface $eventDispatcher;
    private ManagerRegistry $doctrine;
    private OrderItemRepository $orderItemRepository;
    private ErpEntityProvider $erpEntityProvider;

    public function __construct(
        ProductRepository $productRepository,
        ProductFactoryInterface $productFactory,
        ProductVariantRepository $productVariantRepository,
        ProductVariantFactoryInterface $productVariantFactory,
        ProductAdapter $productAdapter,
        ProductVariantAdapter $productVariantAdapter,
        EntityManagerInterface $entityManager,
        LoggerInterface $erpImportLogger,
        ErpEntityProvider $erpEntityProvider,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        OrderItemRepository $orderItemRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->productVariantFactory = $productVariantFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->productAdapter = $productAdapter;
        $this->productVariantAdapter = $productVariantAdapter;
        $this->entityManager = $entityManager;
        $this->erpImportLogger = $erpImportLogger;
        $this->erpEntityProvider = $erpEntityProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Process an ErpItem
     * @param ErpItemModel $erpItem
     * @throws ErpException
     */
    public function transform(ErpItemModel $erpItem): void
    {
        // Check valid response and valid id / code
        if (!$erpItem->isValid()) {
            $this->erpImportLogger->error("Error in ERP item response: " . json_encode($erpItem->getStatus()->statusDetail));
            return;
        }

        $this->erpImportLogger->info(sprintf("Import ERP item with with internalId=%s, code=%s", $erpItem->getId(), $erpItem->getCode()));

        if ($erpItem->isStandalone()) { // A standalone has only one product variant
            $this->transformProduct($erpItem);
            $this->transformProductVariant($erpItem);
        } elseif ($erpItem->isParent()) {
            $this->transformProduct($erpItem);
        } elseif ($erpItem->isChild()) {
            $this->transformProductVariant($erpItem);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Clean ERP result for better view when dump.
     * @param array $haystack
     * @return array
     */
    private function arrayRemoveEmpty(array $haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->arrayRemoveEmpty($value);
            }

            if (is_null($haystack[$key])) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }

    /**
     * @param ErpItemModel $erpItem
     * @throws ErpException
     */
    private function transformProduct(ErpItemModel $erpItem): void
    {
        /** @var Product|null $product */
        $product = $this->findProduct($erpItem->getId(), $erpItem);

        if (true === $erpItem->isSkipped()) {
            $this->erpImportLogger->info(sprintf("[PRODUCT][SKIPPED] Product (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));

            if (null !== $product) {
                if ($this->productIsUsed($product)) {
                    $this->erpImportLogger->alert(sprintf("[PRODUCT][DISABLED] Product is in use! It has been disabled (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                    $product->disable();
                    $this->entityManager->flush();
                } else {
                    $this->erpImportLogger->alert(sprintf("[PRODUCT][REMOVED] Product not used. Is has been deleted (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                    $this->productRepository->remove($product);
                    try {
                        $this->entityManager->flush();
                    } catch (\Exception $e) {
                        $this->erpImportLogger->critical(sprintf("[PRODUCT] Error while deleting a product in use (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                        $this->erpImportLogger->critical($e->getMessage());
                        throw new ErpException(sprintf("[PRODUCT] Error while deleting a product in use (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                    }
                }
            }
            return;
        }

        if (null === $product) {
            $product = $this->initializeNewProduct($erpItem);
            $dispatchType = 'create';
        } else {
            $dispatchType = 'update';
        }

        $this->productAdapter->adaptProduct($product, $erpItem);

        try {
            $this->dispatchElement($product, $dispatchType);
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->erpImportLogger->critical(sprintf("[PRODUCT] Product CODE/SKU is duplicated (internalId=%s, code=%s): %s", $erpItem->getId(), $erpItem->getCode(), $e->getMessage()));
            // reset doctrine & default channel after doctrine issue.
            $this->doctrine->resetManager();
        } catch (\Exception $e) {
            $this->erpImportLogger->error(sprintf("[PRODUCT] ERROR internalId=%s, code=%s", $erpItem->getId(), $erpItem->getCode()));
            $this->erpImportLogger->critical($e->getMessage());
        }
    }

    /**
     * @param ErpItemModel $erpItem
     * @throws ErpException
     */
    private function transformProductVariant(ErpItemModel $erpItem): void
    {
        /** @var ProductVariant|null $productVariant */
        $productVariant = $this->findProductVariant($erpItem->getId(), $erpItem); // Search existing product variant from the erpId

        if (true === $erpItem->isSkipped()) {
            $this->erpImportLogger->info(sprintf("[PRODUCT-VARIANT][SKIPPED] ProductVariant (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));

            if (null !== $productVariant) {
                // Check if product variant exists and delete or disable it
                if ($this->productVariantIsUsed($productVariant)) {
                    $this->erpImportLogger->alert(sprintf("[PRODUCT-VARIANT][DISABLED] ProductVariant is in use! It has been disabled (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                    $productVariant->disable();
                    $this->entityManager->flush();
                } else {
                    $this->erpImportLogger->alert(sprintf("[PRODUCT-VARIANT][REMOVED] ProductVariant not used. Is has been deleted (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                    try {
                        $this->productVariantRepository->remove($productVariant);
                    } catch (\Exception $e) {
                        $this->erpImportLogger->critical(sprintf("[PRODUCT-VARIANT] Error while deleting a productVariant in use (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                        $this->erpImportLogger->critical($e->getMessage());
                        throw new ErpException(sprintf("[PRODUCT-VARIANT] Error while deleting a productVariant in use (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
                    }
                }
            }
            return;
        }

        if (null === $productVariant) {
            $productVariant = $this->initializeNewProductVariant($erpItem);
            $dispatchType = 'create';
        } else {
            $dispatchType = 'update';
        }

        $this->productVariantAdapter->adaptProductVariant($productVariant, $erpItem);

        if (null === $productVariant->getProduct()) {
            if ($productVariant->isEnabled()) {
                $this->erpImportLogger->error(sprintf("[PRODUCT-VARIANT] Variant has no parent product (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));
            }
            return;
        }

        try {
            $this->dispatchElement($productVariant, $dispatchType);
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->erpImportLogger->critical(sprintf("[PRODUCT-VARIANT] ProductVariant CODE/SKU is duplicated (internalId=%s, code=%s): %s", $erpItem->getId(), $erpItem->getCode(), $e->getMessage()));
            // reset doctrine & default channel after doctrine issue.
            $this->doctrine->resetManager();
        } catch (\Exception $e) {
            $this->erpImportLogger->error(sprintf("[PRODUCT-VARIANT] ERROR internalId=%s, code=%s", $erpItem->getId(), $erpItem->getCode()));
            $this->erpImportLogger->critical($e->getMessage());
        }
    }

    /**
     * Create a new product linked with an ErpEntity
     * @param ErpItemModel $erpItem
     * @return Product
     */
    private function initializeNewProduct(ErpItemModel $erpItem): Product
    {
        $this->erpImportLogger->info(sprintf("[PRODUCT] Creating new product (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));

        /** @var Product|null $product */
        $product = $this->productFactory->createNew();
        $product->setEnabled(false); // All new products are disabled by default !

        $erpEntity = $this->erpEntityProvider->provide(ErpEntity::TYPE_PRODUCT, $erpItem->getId(), $erpItem->getDisplayName());

        $product->setErpEntity($erpEntity);

        return $product;
    }

    /**
     * Create a new product Variant linked with an ErpEntity
     * @param ErpItemModel $erpItem
     * @return ProductVariant
     */
    private function initializeNewProductVariant(ErpItemModel $erpItem): ProductVariant
    {
        $this->erpImportLogger->info(sprintf("[PRODUCT-VARIANT] Creating new product variant (internalId=%s, code=%s)", $erpItem->getId(), $erpItem->getCode()));

        /** @var ProductVariant $productVariant */
        $productVariant = $this->productVariantFactory->createNew();

        $erpEntity = $this->erpEntityProvider->provide(ErpEntity::TYPE_PRODUCT_VARIANT, $erpItem->getId(), $erpItem->getDisplayName());

        $productVariant->setErpEntity($erpEntity);

        return $productVariant;
    }

    /**
     * @param object|null $object
     * @param string $type == 'create' | 'update'
     */
    private function dispatchElement(?object $object, string $type): void
    {
        if ($object instanceof Product) {
            $preEventName = 'sylius.product.pre_' . $type;
            $postEventName = 'sylius.product.post_' . $type;
        } elseif ($object instanceof ProductVariant) {
            $preEventName = 'sylius.product_variant.pre_' . $type;
            $postEventName = 'sylius.product_variant.post_' . $type;
        } else {
            return;
        }

        // Persist & flush with events
        /** @var GenericEvent $event */
        $event = $this->eventDispatcher->dispatch(new GenericEvent($object), $preEventName);
        if ($event->isPropagationStopped()) {
            return;
        }
        $this->productRepository->add($object);
        $this->eventDispatcher->dispatch(new GenericEvent($object), $postEventName);
    }

    /**
     * Check if product can be deleted.
     * @param ProductInterface $product
     * @return bool
     */
    private function productIsUsed(ProductInterface $product) :bool
    {
        /** @var  ProductVariant $productVariant */
        foreach ($product->getVariants() as $productVariant) {
            if ($this->productVariantIsUsed($productVariant)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Search is a product variant is used by an order
     * @param ProductVariantInterface $productVariant
     * @return bool
     */
    private function productVariantIsUsed(ProductVariantInterface $productVariant) :bool
    {
        // If is used in orders.
        if (null === $this->orderItemRepository->findOneBy(['variant' => $productVariant])) {
            return false;
        }
        return true;
    }

    /**
     * Find product by ERP ID or CODE and assure that the association with ErpEntity is valid
     * @param int $erpId
     * @param ErpItemModel $erpItem
     * @return ProductInterface|null
     */
    private function findProduct(int $erpId, ErpItemModel $erpItem) :?ProductInterface
    {
        /**
         * 1. Search by ERP ID
         */
        if (null !== $product = $this->productRepository->findOneByErpId($erpId)) {
            return $product;
        }

        /**
         * 2. Product ERP association not found => Search by CODE and RE-associate with ErpEntity
         */
        if (null !== $erpItem->getCode() && null !== $product = $this->productRepository->findOneByCode($erpItem->getCode())) {
            $erpEntity = $this->erpEntityProvider->provide(ErpEntity::TYPE_PRODUCT, $erpId, $erpItem->getDisplayName());
            /** @var Product $product */
            $product->setErpEntity($erpEntity);
            $this->entityManager->flush();

            $this->erpImportLogger->critical(sprintf("[PRODUCT] Product (internalId=%s, code=%s) RE-associated to sylius ID => %d", $erpItem->getId(), $erpItem->getCode(), $product->getId()));
            return $product;
        }

        return null;
    }

    /**
     * Find product by ERP ID or CODE and assure that the association with ErpEntity is valid
     * @param int $erpId
     * @param ErpItemModel $erpItem
     * @return ProductVariantInterface|null
     */
    private function findProductVariant(int $erpId, ErpItemModel $erpItem) :?ProductVariantInterface
    {
        /**
         * 1. Search by ERP ID
         */
        if (null !== $productVariant = $this->productVariantRepository->findOneByErpId($erpId)) {
            return $productVariant;
        }

        /**
         * 2. ProductVariant ERP association not found => Search by CODE and RE-associate with ErpEntity
         * @var ?ProductVariant $productVariant
         */

        if (null !== $erpItem->getCode() && null !== $productVariant = $this->productVariantRepository->findOneBy(['code' => $erpItem->getCode()])) {
            $erpEntity = $this->erpEntityProvider->provide(ErpEntity::TYPE_PRODUCT_VARIANT, $erpId, $erpItem->getDisplayName());
            $productVariant->setErpEntity($erpEntity);
            $this->entityManager->flush();

            $this->erpImportLogger->critical(sprintf("[PRODUCT-VARIANT] ProductVariant (internalId=%s, code=%s) RE-associated to sylius ID => %d", $erpItem->getId(), $erpItem->getCode(), $productVariant->getId()));
            return $productVariant;
        }

        return null;
    }
}
