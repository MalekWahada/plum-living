<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Taxonomy\Taxon;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductTaxon as BaseProductTaxon;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_taxon")
 *
 * @method Taxon|null getTaxon()
 */
class ProductTaxon extends BaseProductTaxon
{
}
