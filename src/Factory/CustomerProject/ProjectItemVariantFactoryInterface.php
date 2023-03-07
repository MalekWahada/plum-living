<?php

declare(strict_types=1);

namespace App\Factory\CustomerProject;

use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductVariant;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\TranslatableFactoryInterface;

interface ProjectItemVariantFactoryInterface extends TranslatableFactoryInterface
{
    /**
     * @return ProjectItemVariant
     */
    public function createNew(): ProjectItemVariant;

    /**
     * $details array comes from PlumScanner API and should have the following format :
     * [
     *      'SyliusProductOptions' => [
     *            'SKU' => string,
     *            'SyliusId' => integer($int32),
     *            'Quantity' => integer($int32),
     *      ],
     * ]
     *
     * For further details check ProjectItemFactoryInterface::createForProjectWithDetails docs
     *
     * @param ProductVariant $productVariant
     * @param array $details
     * @return ProjectItemVariant
     */
    public function createForProductVariantWithScannerDetails(
        ProductVariant $productVariant,
        array $details
    ): ProjectItemVariant;

    public function createCloneForVariant(ProjectItemVariant $variant): ProjectItemVariant;

    public function createForProductVariant(ProductVariantInterface $variant, int $quantity = 1): ProjectItemVariant;
}
