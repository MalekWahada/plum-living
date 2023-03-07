<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Channel\Channel;
use App\Entity\Erp\ErpEntity;
use App\Entity\Product\Product;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    /**
     * @return null|Product[]
     */
    public function getEnabledProductsByFacadeDesignFinishColor(
        Taxon $facadeTypeTaxon,
        Taxon $accessoryTaxon,
        ProductOptionValue $design,
        ProductOptionValue $finish,
        ProductOptionValue $color,
        string $channelCode
    ): ?array {
        $qb = $this->createQueryBuilder('p');

        $subQueryAccessory = $this->createQueryBuilder('pa')
            ->innerJoin('pa.variants', 'pav')
            ->andWhere('pav.enabled = 1')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gt('pav.onHand', 0),
                $qb->expr()->neq('pav.tracked', 1),
            ))
            ->andWhere('pa.enabled = 1')
            ->andWhere('pa.mainTaxon = :accessoryTaxon')
            ->innerJoin('pa.channels', 'aChannel', Join::WITH, 'aChannel.code = :channelCode')
            ->select('pa.id')
        ;

        return $qb
            ->andWhere('p.mainTaxon =:facadeTypeTaxon')
            ->andWhere('p.enabled = 1')
            ->innerJoin('p.channels', 'pChannel', Join::WITH, 'pChannel.code = :channelCode')
            ->andWhere('pv.enabled = 1')
            ->select('p')
            ->leftJoin('p.images', 'pi')
            ->innerJoin('p.variants', 'pv')
            ->innerJoin(
                'pv.optionValues',
                'designOption',
                Join::WITH,
                (string) $qb->expr()->orX(
                    $qb->expr()->eq('designOption', ':design'),
                    $qb->expr()->eq('designOption.code', ':design_unique'),
                )
            )
            ->innerJoin('pv.optionValues', 'finishOption', 'WITH', 'finishOption = :finish')
            ->innerJoin('pv.optionValues', 'colorOption', 'WITH', 'colorOption = :color')
            ->innerJoin('pv.channelPricings', 'pvChannelPricing', Join::WITH, 'pvChannelPricing.channelCode = :channelCode')
            ->leftJoin(
                'App\Entity\Product\Product',
                'accessory',
                Join::WITH,
                'accessory.id IN (' . $subQueryAccessory->getQuery()->getDQL() . ')'
            )//check for existing accessory variants
            ->leftJoin('accessory.images', 'ai')
            // to order by the main taxonomy position and product name alphabetically
            ->leftJoin('p.mainTaxon', 'pmt')
            ->leftJoin('p.productTaxons', 'ptx', Join::WITH, 'ptx.taxon = pmt')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('accessory')//add accessory
            ->addSelect('ai')
            ->addSelect('pi')
            ->addSelect('pv')
            ->addSelect('pvChannelPricing')
            ->setParameter('accessoryTaxon', $accessoryTaxon)//add accessory
            ->setParameter('facadeTypeTaxon', $facadeTypeTaxon)
            ->setParameter('design', $design)
            ->setParameter('design_unique', ProductOptionValue::DESIGN_UNIQUE_CODE)
            ->setParameter('finish', $finish)
            ->setParameter('color', $color)
            ->setParameter('channelCode', $channelCode)
            ->orderBy('p.position', Criteria::ASC)
            ->addOrderBy('accessory.position', Criteria::ASC)
            ->addOrderBy('ptx.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    public function findOneByErpId(int $erpInternalId): ?Product
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.erpEntity', 'e')
            ->where('e.erpId = :id')
            ->andWhere('e.type = :type')
            ->setParameter('id', $erpInternalId)
            ->setParameter('type', ErpEntity::TYPE_PRODUCT)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAvailableProductsGetQb(string $facadeTypeTaxonCode): QueryBuilder
    {
        return $this->createQueryBuilder('product')
            ->where('product.enabled = 1')
            ->innerJoin('product.mainTaxon', 'taxon')
            ->andWhere("taxon.code IN (:taxons)")
            ->join('product.variants', 'variants')
            ->groupBy('product.id')
            ->having('COUNT(variants) > 0')
            ->setParameter('taxons', [
                $facadeTypeTaxonCode,
                Taxon::TAXON_ACCESSORY_CODE,
            ]);
    }

    public function findOneByIdAndTaxonCode(int $productId, string $facadeTypeTaxonCode): ?Product
    {
        try {
            return $this->createQueryBuilder('product')
                ->innerJoin('product.mainTaxon', 'taxon')
                ->where('taxon.code = :facadeTypeCode')
                ->andwhere('product.id =:id')
                ->setParameter('id', $productId)
                ->setParameter('facadeTypeCode', $facadeTypeTaxonCode)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getProductsByMainTaxon(string $mainTaxonCode, string $channelCode): ?array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.enabled = 1')
        ;
        return $qb->innerJoin('p.variants', 'pv')
            ->innerJoin('p.channels', 'pChannel', Join::WITH, 'pChannel.code = :channelCode')
            ->leftJoin('p.mainTaxon', 'taxon')
            ->andWhere('pv.enabled = 1')
            ->andWhere('pv.onHand > 0 or pv.tracked != 1')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gt('pv.onHand', 0),
                $qb->expr()->neq('pv.tracked', 1),
            ))
            ->andWhere('taxon.code = :mainTaxonCode')
            ->setParameter('mainTaxonCode', $mainTaxonCode)
            ->setParameter('channelCode', $channelCode)
            ->orderBy('p.position', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }
}
