<?php

declare(strict_types=1);

namespace App\Validator\CMSContentConstraint;

use App\Provider\CMS\PagesSkeleton\PagesSkeletonProvider;

class ProductCompleteInfoContent extends CMSContent
{
    public function __construct($options)
    {
        $options['type'] = PagesSkeletonProvider::UI_SKELETON_PRODUCT_INFORMATION;
        parent::__construct($options);
    }

    public function validatedBy(): string
    {
        return CMSContentValidator::class;
    }
}
