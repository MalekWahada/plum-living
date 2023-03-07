<?php

declare(strict_types=1);

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

/**
 * This filter is an "exists" filter with a "all" option used to skip itself from filtering
 */
final class SkippableExistsFilter implements FilterInterface
{
    public const TRUE = 'true';

    public const FALSE = 'false';

    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (null === $data || "" === $data) { // Case 'all'
            return;
        }

        $field = $options['field'] ?? $name;

        if (self::TRUE === $data) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->isNotNull($field));

            return;
        }

        $dataSource->restrict($dataSource->getExpressionBuilder()->isNull($field));
    }
}
