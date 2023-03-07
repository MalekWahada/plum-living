<?php

declare(strict_types=1);

namespace App\Entity\Taxation;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxation\Model\TaxCategory as BaseTaxCategory;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_tax_category")
 */
class TaxCategory extends BaseTaxCategory
{
    public const DEFAULT_TAX_CATEGORY_CODE = 'tva';
}
