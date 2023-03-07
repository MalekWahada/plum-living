<?php

declare(strict_types=1);

namespace App\Validator\CMSContentConstraint;

use App\Provider\CMS\PagesSkeleton\PagesSkeletonProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class CMSContent extends Constraint
{
    public string $message = 'app.cms_content';

    public string $type;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!isset($options['type'])) {
            throw new MissingOptionsException(sprintf('Option "type" must be specified for constraint "%s".', __CLASS__), ['type']);
        } elseif (!$this->isTypeValid($options)) {
            throw new InvalidArgumentException('The given argument is not valid');
        }
    }

    public function getTargets(): array
    {
        return [
            self::PROPERTY_CONSTRAINT,
        ];
    }

    private function isTypeValid(array $options = null): bool
    {
        if (!isset($options['type']) ||
            !in_array($options['type'], PagesSkeletonProvider::SKELETON_UI_TYPES, true)
        ) {
            return false;
        }
        return true;
    }
}
