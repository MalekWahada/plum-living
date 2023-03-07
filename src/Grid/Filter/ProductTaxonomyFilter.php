<?php

declare(strict_types=1);

namespace App\Grid\Filter;

use App\Entity\Taxonomy\Taxon;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ProductTaxonomyFilter implements FilterInterface
{
    private RepositoryInterface $taxonRepository;

    public function __construct(RepositoryInterface $taxonRepository)
    {
        $this->taxonRepository = $taxonRepository;
    }

    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $data['taxonomy']]);

        if (null === $taxon) {
            return;
        }

        $expression = $dataSource->getExpressionBuilder()->equals('productTaxons.taxon.code', $taxon->getCode());

        $dataSource->restrict($expression);
    }
}
