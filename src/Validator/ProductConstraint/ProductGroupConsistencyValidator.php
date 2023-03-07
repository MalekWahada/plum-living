<?php

declare(strict_types=1);

namespace App\Validator\ProductConstraint;

use App\Entity\Product\ProductGroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProductGroupConsistencyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductGroupConsistency) {
            throw new UnexpectedTypeException($constraint, ProductGroupConsistency::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof ProductGroup) {
            throw new UnexpectedTypeException($value, ProductGroup::class);
        }

        $options = null;
        $commonTaxons = [];

        // All products must have the same options and common taxons
        foreach ($value->getProducts() as $product) {
            if (null !== $options && $options !== $product->getOptions()->toArray()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('products')
                    ->addViolation();
                return;
            }

            $productTaxons = array_merge([$product->getMainTaxon()], $product->getTaxons()->toArray());
            if (!empty($commonTaxons) && !$this->hasTaxonsInCommon($productTaxons, $commonTaxons)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('products')
                    ->addViolation();
                return;
            }
            if (empty($commonTaxons)) {
                $commonTaxons = $productTaxons;
            } else {
                $commonTaxons = $this->getCommonTaxons($productTaxons, $commonTaxons);
            }
            $options = $product->getOptions()->toArray();
        }
    }

    private function getCommonTaxons(array $source, array $target): array
    {
        $commonTaxons = [];
        foreach ($source as $taxon) {
            if (in_array($taxon, $target, true)) {
                $commonTaxons[] = $taxon;
            }
        }

        return array_unique($commonTaxons);
    }

    private function hasTaxonsInCommon(array $source, array $target): bool
    {
        foreach ($source as $taxon) {
            if (in_array($taxon, $target, true)) {
                return true;
            }
        }

        return false;
    }
}
