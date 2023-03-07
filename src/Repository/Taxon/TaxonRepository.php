<?php

declare(strict_types=1);

namespace App\Repository\Taxon;

use App\Entity\Taxonomy\Taxon;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository as BaseTaxonRepository;

class TaxonRepository extends BaseTaxonRepository
{
    public const FACADE_TYPE_TAXONS = [
        Taxon::TAXON_FACADE_METOD,
        Taxon::TAXON_FACADE_PAX,
    ];

    /**
     * @return Taxon[]
     */
    public function findChoicesFacadeTypes() : array
    {
        return $this->findBy(['code' =>  self::FACADE_TYPE_TAXONS]);
    }

    public function findTaxonFacadeType(string $facadeCode): ?Taxon
    {
        if (!in_array(strtolower($facadeCode), self::FACADE_TYPE_TAXONS, true)) {
            return null;
        }

        try {
            return $this->createQueryBuilder('taxon')
                ->where('taxon.code = :code')
                ->setParameter('code', $facadeCode)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
