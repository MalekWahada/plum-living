<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use App\Entity\Product\ProductVariant;

class ProductOptionValueRepository extends EntityRepository
{
    public function findOneByCodeAndOptionCode(string $code, string $optionCode): ?ProductOptionValue
    {
        try {
            return $this->createQueryBuilder('o')
                ->innerJoin('o.option', 'option')
                ->andWhere('o.code = :code AND option.code = :option_code')
                ->setParameter('code', $code)
                ->setParameter('option_code', $optionCode)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * getOptionValuesByVariants
     *
     * @param string $optionCode
     * @param Taxon|null $facadeType
     * @param ProductOptionValue|null $design
     * @param ProductOptionValue|null $finish
     * @return array|null|ProductOptionValue[]
     */
    public function getOptionValuesByVariants(
        string $optionCode,
        ?Taxon $facadeType = null,
        ?ProductOptionValue $design = null,
        ?ProductOptionValue $finish = null
    ): ?array {
        //get the ids of product variants has option value $facadeType
        $variants = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(ProductVariant::class, 'subProductVariant')
            ->select('subProductVariant.id')
            ->distinct()
            ->innerJoin('subProductVariant.product', 'product')
            ->andWhere('subProductVariant.enabled = 1')
            ->andWhere('product.mainTaxon = :facadeType');

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(ProductVariant::class, 'productVariant');

        if (null !== $design) {
            //get the ids of product variants has option values  $facadeType and $design
            $variants
                ->innerJoin('subProductVariant.optionValues', 'subProductOptionValue1')
                ->andWhere('subProductOptionValue1.id = :design');
            $qb->setParameter('design', $design);
        }

        if (null !== $finish) {
            //get the ids of product variants has taxon $facadeType & option values $design and finish
            $variants
                ->innerJoin('subProductVariant.optionValues', 'subProductOptionValue2')
                ->andWhere('subProductOptionValue2.id = :finish');
            $qb->setParameter('finish', $finish);
        }
        return $qb->innerJoin('productVariant.optionValues', 'optionValue')
            ->innerJoin(
                ProductOptionValue::class,
                'productOptionValue',
                'WITH',
                'productOptionValue.id = optionValue'
            )
            ->innerJoin('productOptionValue.option', 'option')
            ->innerJoin('productOptionValue.images', 'povi') // Option value must have an image to be listed
            ->innerJoin('productOptionValue.translations', 'povt')
            ->select('productOptionValue')
            ->addSelect('povi')
            ->addSelect('povt')
            ->distinct()
            ->where($qb->expr()->In('productVariant.id', $variants->getDQL()))
            ->andWhere('option.code = :option_code')
            ->setParameter('facadeType', $facadeType)
            ->setParameter('option_code', $optionCode)
            ->orderBy('productOptionValue.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array|null|ProductOptionValue[]
     */
    public function getEnabledColors(): ?array
    {
        // todo : Wissem : do automatic join ?
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->from(ProductVariant::class, 'o')
            ->innerJoin('o.optionValues', 'optionValue')
            ->leftJoin(ProductOptionValue::class, 'productOptionValue', 'WITH', 'productOptionValue.id = optionValue')
            ->andWhere('o.enabled = 1')
            ->leftJoin('productOptionValue.option', 'option')
            ->select('productOptionValue')
            ->andWhere('option.code = :option_code')
            ->setParameter('option_code', 'color')
            ->orderBy('productOptionValue.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $optionCode
     * @param bool $allowHidden
     * @return QueryBuilder
     */
    public function findByOptionCodeGetQb(string $optionCode, bool $allowHidden = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('pov');
        $qb->innerJoin('pov.option', 'po');
        $qb->andWhere('po.code = :optionCode');
        $qb->innerJoin(ProductVariant::class, 'pv');
        $qb->andWhere('pov MEMBER OF pv.optionValues');
        $qb->andWhere('pv.enabled = 1');

        if (!$allowHidden) {
            $qb->andWhere($qb->expr()->notIn('pov.code', ProductOptionValue::HIDDEN_COLORS));
        }

        if (!$allowHidden && $optionCode === ProductOption::PRODUCT_OPTION_DESIGN) {
            $qb->andWhere($qb->expr()->neq('pov.code', ':design_unique_code'));
            $qb->setParameter('design_unique_code', ProductOptionValue::DESIGN_UNIQUE_CODE);
        }

        $qb->setParameter('optionCode', $optionCode);
        $qb->orderBy('pov.position', Criteria::ASC);

        return $qb;
    }

    /**
     * @param string $optionCode
     * @return array|ProductOptionValue[]
     */
    public function findByOptionCode(string $optionCode): array
    {
        return $this->findByOptionCodeGetQb($optionCode)->getQuery()->getResult();
    }

    /**
     * @param string $code
     * @param string $optionCode
     * @return ProductOptionValue|null
     * @throws NonUniqueResultException
     */
    public function finOneByCodeAndOptionCode(string $code, string $optionCode): ?ProductOptionValue
    {
        return $this->createQueryBuilder('pov')
            ->innerJoin('pov.option', 'po')
            ->andWhere('po.code = :optionCode')
            ->andWhere('pov.code = :code')
            ->setParameter('code', $code)
            ->setParameter('optionCode', $optionCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return option by erp value.
     * @param string $optionCode
     * @param int $id
     * @return ProductOptionValue|null
     * @throws NonUniqueResultException
     */
    public function findByByErpId(string $optionCode, int $id): ?ProductOptionValue
    {
        return $this->createQueryBuilder('pov')
            ->select('pov')
            ->innerJoin('pov.option', 'po')
            ->innerJoin('pov.erpEntities', 'e')
            ->where('e.erpId = :id')
            ->andWhere('e.type = :optionCode')
            ->andWhere('po.code = :optionCode')
            ->setParameter('id', $id)
            ->setParameter('optionCode', $optionCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $optionCode
     * @param array $optionValueCodes
     * @return QueryBuilder|null
     */
    public function findByOptionAndCodesGetQb(string $optionCode, array $optionValueCodes): ?QueryBuilder
    {
        $qb = $this->createQueryBuilder('pov');
        $qb->innerJoin('pov.option', 'po');
        $qb->Where('po.code = :optionCode');
        $qb->andWhere($qb->expr()->in('pov.code', ':optionValueCodes'));
        $qb->setParameters([
            'optionCode' => $optionCode,
            'optionValueCodes' => $optionValueCodes,
        ]);

        return $qb->orderBy('pov.position', Criteria::ASC);
    }

    /**
     * @param string $optionCode
     * @param array $optionValueCodes
     * @return array|null|ProductOptionValue[]
     */
    public function findByOptionAndCodes(string $optionCode, array $optionValueCodes): ?array
    {
        return $this->findByOptionAndCodesGetQb($optionCode, $optionValueCodes)->getQuery()->getResult();
    }
}
