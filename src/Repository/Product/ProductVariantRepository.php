<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Erp\ErpEntity;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductVariant;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository as BaseProductVariantRepository;

class ProductVariantRepository extends BaseProductVariantRepository
{
    public function findVariantByOptionValues(
        int $productId,
        string $facadeTypeCode,
        string $designCode,
        string $finishCode,
        string $colorCode
    ): ?ProductVariant {
        try {
            return $this->createQueryBuilder('p')
                ->innerJoin('p.product', 'product')
                ->where('product.id = :id')
                ->setParameter('id', $productId)
                ->innerJoin('product.mainTaxon', 'taxon')
                ->andWhere('taxon.code = :facadeTypeCode')
                ->setParameter('facadeTypeCode', $facadeTypeCode)
                ->innerJoin('p.optionValues', 'design', 'with', 'design.code = :designCode')
                ->setParameter('designCode', $designCode)
                ->innerJoin('p.optionValues', 'finish', 'with', 'finish.code = :finishCode')
                ->setParameter('finishCode', $finishCode)
                ->innerJoin('p.optionValues', 'color', 'with', 'color.code = :colorCode')
                ->setParameter('colorCode', $colorCode)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            error_log($e->getMessage());

            return null;
        }
    }


    /**
     * Return product from
     * @param int $erpInternalId
     * @return null|ProductVariant
     */
    public function findOneByErpId(int $erpInternalId): ?ProductVariant
    {
        try {
            return $this->createQueryBuilder('pv')
                ->select('pv')
                ->innerJoin('pv.erpEntity', 'e')
                ->where('e.erpId = :id')
                ->andWhere('e.type = :type')
                ->setParameter('type', ErpEntity::TYPE_PRODUCT_VARIANT)
                ->setParameter('id', $erpInternalId)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            error_log($e->getMessage());

            return null;
        }
    }

    public function findByCombination(
        int $productId,
        string $facadeTypeCode,
        string $designCode = null,
        string $finishCode = null,
        string $colorCode = null
    ): ?array {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.product', 'product')
            ->where('product.id = :id')
            ->setParameter('id', $productId)
            ->innerJoin('product.mainTaxon', 'taxon')
            ->andWhere('taxon.code = :facadeTypeCode')
            ->setParameter('facadeTypeCode', $facadeTypeCode);

        if (null !== $designCode) {
            $query->innerJoin('p.optionValues', 'design', 'with', 'design.code = :designCode');
            $query->setParameter('designCode', $designCode);
        }

        if (null !== $finishCode) {
            $query->innerJoin('p.optionValues', 'finish', 'with', 'finish.code = :finishCode');
            $query->setParameter('finishCode', $finishCode);
        }

        if (null !== $colorCode) {
            $query->innerJoin('p.optionValues', 'color', 'with', 'color.code = :colorCode');
            $query->setParameter('colorCode', $colorCode);
        }

        return $query->getQuery()->getResult();
    }

    public function findOneByProductCodeAndOptionValue(
        string $productCode,
        string $optionValueCode,
        string $option = ProductOption::PRODUCT_OPTION_COLOR
    ): ?ProductVariant {
        if (!in_array($option, ProductOption::FACADE_SELECTED_OPTIONS)) {
            return null;
        }

        $qb = $this->createQueryBuilder('pv');
        $qb->innerJoin('pv.product', 'product');
        $qb->where('product.code = :productCode');
        $qb->innerJoin('pv.optionValues', $option, Join::WITH, $option .'.code = :optionValueCode');
        $qb->setParameters([
            'productCode' => $productCode,
            'optionValueCode' => $optionValueCode,
        ]);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function createListQueryBuilder(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale);
    }

    public function getVariantsByCodeOrName(string $locale, string $term, ?int $limit = 50): ?array
    {
        return $this->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->innerJoin('pv.translations', 'pvt')
            ->andWhere('p.enabled = 1')
            ->andWhere('pvt.locale = :locale')
            ->andWhere('pv.code LIKE :term OR pvt.name LIKE :term')
            ->setParameter('locale', $locale)
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
