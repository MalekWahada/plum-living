<?php

declare(strict_types=1);

namespace App\Provider\Dashboard;

use Sylius\Component\Core\Dashboard\Interval;
use Sylius\Component\Core\Dashboard\SalesDataProviderInterface;
use Sylius\Component\Core\Dashboard\SalesSummary;
use Sylius\Component\Core\Dashboard\SalesSummaryInterface;
use Sylius\Component\Core\Model\ChannelInterface;

class SalesDataProvider implements SalesDataProviderInterface
{
    private SalesDataProviderInterface $decoratedProvider;

    public function __construct(SalesDataProviderInterface $decoratedProvider)
    {
        $this->decoratedProvider = $decoratedProvider;
    }

    public function getSalesSummary(ChannelInterface $channel, \DateTimeInterface $startDate, \DateTimeInterface $endDate, Interval $interval): SalesSummaryInterface
    {
        $summary =  $this->decoratedProvider->getSalesSummary($channel, $startDate, $endDate, $interval);
        $formattedSales = array_map(
            static function (int $total): string {
                return number_format(abs($total / 10), 2, '.', ''); // Need to divide 10x more to get divider 1000
            },
            $summary->getSales()
        );
        
        $arrayCombine = array_combine($summary->getIntervals(), $formattedSales);
        \assert(is_array($arrayCombine));
        
        return new SalesSummary($arrayCombine);
    }
}
