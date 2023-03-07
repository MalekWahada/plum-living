<?php

declare(strict_types=1);

namespace App\Repository\Product;

use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductAttributeValueRepository as BaseProductAttributeValueRepository;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;

class ProductAttributeValueRepository extends BaseProductAttributeValueRepository implements ProductAttributeValueRepositoryInterface
{
    public function findByProductAndAttribute(ProductInterface $product, AttributeInterface $attribute): array
    {
        if (null === $product->getId()) { // An inserting product cannot have attribute values.
            return [];
        }

        return $this->createQueryBuilder('o')
            ->join('o.attribute', 'a')
            ->andWhere('o.subject = :product')
            ->andWhere('a.code = :code')
            ->setParameter('product', $product)
            ->setParameter('code', $attribute->getCode())
            ->getQuery()
            ->getResult()
        ;
    }
}
