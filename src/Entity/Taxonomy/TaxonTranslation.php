<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxonomy\Model\TaxonTranslation as BaseTaxonTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon_translation")
 */
class TaxonTranslation extends BaseTaxonTranslation
{
    public const PUBLISHED_LOCALE = 'fr';

    /**
     * @ORM\Column(name="product_info", type="text", nullable=true)
     */
    protected ?string $productInfo = null;

    public function getProductInfo(): ?string
    {
        return $this->productInfo;
    }

    public function setProductInfo(?string $productInfo): void
    {
        $this->productInfo = $productInfo;
    }
}
