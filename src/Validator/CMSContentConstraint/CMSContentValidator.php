<?php

declare(strict_types=1);

namespace App\Validator\CMSContentConstraint;

use App\Checker\ProductInfoContentChecker;
use App\Provider\CMS\PagesSkeleton\PagesSkeletonProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CMSContentValidator extends ConstraintValidator
{
    private ProductInfoContentChecker $contentChecker;

    public function __construct(ProductInfoContentChecker $contentChecker)
    {
        $this->contentChecker = $contentChecker;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CMSContent) {
            throw new UnexpectedTypeException($constraint, CMSContent::class);
        }

        if (null === $value || !is_string($value)) {
            return;
        }

        if ($constraint->type === PagesSkeletonProvider::UI_SKELETON_PRODUCT_INFORMATION &&
            !$this->contentChecker->checkProductInformation($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
