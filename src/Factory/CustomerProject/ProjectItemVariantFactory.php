<?php

declare(strict_types=1);

namespace App\Factory\CustomerProject;

use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductVariant;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ProjectItemVariantFactory implements ProjectItemVariantFactoryInterface
{
    private FactoryInterface $decoratedFactory;

    public function __construct(
        FactoryInterface $decoratedFactory
    ) {
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createNew(): ProjectItemVariant
    {
        /** @var ProjectItemVariant $variant */
        $variant = $this->decoratedFactory->createNew();

        return $variant;
    }

    /**
     * {@inheritDoc}
     */
    public function createForProductVariantWithScannerDetails(
        ProductVariant $productVariant,
        array $details
    ): ProjectItemVariant {
        $variant = $this->createNew();

        $variant->setQuantity($details['Quantity']);
        $variant->setProductVariant($productVariant);

        return $variant;
    }

    public function createCloneForVariant(ProjectItemVariant $variant): ProjectItemVariant
    {
        $clone = $this->createNew();

        $clone->setQuantity($variant->getQuantity());
        $clone->setProductVariant($variant->getProductVariant());

        return $clone;
    }

    public function createForProductVariant(ProductVariantInterface $variant, int $quantity = 1): ProjectItemVariant
    {
        $itemVariant = $this->createNew();
        $itemVariant->setQuantity($quantity);
        $itemVariant->setProductVariant($variant);
        return $itemVariant;
    }
}
