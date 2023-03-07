<?php

declare(strict_types=1);

namespace App\Grid\Filter;

use App\Model\ProductChannelFilter\ProductChannelFilterModel;
use Sylius\Bundle\GridBundle\Doctrine\PHPCRODM\Driver;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class ProductChannelFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        /** @var string $channelCode */
        $channelCode = $data['channel'] ?? ProductChannelFilterModel::ALL_CHANNELS;
        if ($channelCode !== ProductChannelFilterModel::ALL_CHANNELS) {
            $expressionBuilder = $dataSource->getExpressionBuilder();

            if ($channelCode === ProductChannelFilterModel::NO_CHANNEL) {
                $exprToSeek = Driver::QB_SOURCE_ALIAS . '.channels is empty';
                $expression = $dataSource->getExpressionBuilder()->andX($exprToSeek);
            } else {
                $expression = $expressionBuilder->equals('channels.code', $channelCode);
            }
            $dataSource->restrict($expression);
        }
    }
}
