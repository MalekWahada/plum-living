<?php

declare(strict_types=1);

namespace App\Twig;

use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('get_date_from_days', [$this, 'getDateFromDays'])
        ];
    }

    public function getDateFromDays(int $days): DateTime
    {
        $dateTime = new DateTime();

        return $dateTime->modify('+'.$days.' day');
    }
}
