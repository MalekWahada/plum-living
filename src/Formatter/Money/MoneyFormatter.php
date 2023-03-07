<?php

declare(strict_types=1);

namespace App\Formatter\Money;

use NumberFormatter;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Webmozart\Assert\Assert;

final class MoneyFormatter implements MoneyFormatterInterface
{
    private int $currencyDivisor;

    public function __construct(int $defaultDivisor)
    {
        $this->currencyDivisor = $defaultDivisor;
    }

    public function format(int $amount, string $currencyCode, ?string $locale = null): string
    {
        $formatter = new NumberFormatter($locale ?? 'en', NumberFormatter::CURRENCY);

        return $this->doFormat($formatter, $amount, $currencyCode);
    }

    public function formatWithoutDecimals(int $amount, string $currencyCode, ?string $locale = null): string
    {
        $formatter = new NumberFormatter($locale ?? 'en', NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        return $this->doFormat($formatter, $amount, $currencyCode);
    }

    private function doFormat(NumberFormatter $formatter, int $amount, string $currencyCode): string
    {
        $result = $formatter->formatCurrency(abs($amount / $this->currencyDivisor), $currencyCode);
        Assert::notSame(
            false,
            $result,
            sprintf('The amount "%s" of type %s cannot be formatted to currency "%s".', $amount, gettype($amount), $currencyCode)
        );

        return $amount >= 0 ? $result : '-' . $result;
    }

    public function formatWithDecimals(int $amount, int $decimals = 3): float
    {
        return (float) number_format(
            $amount / $this->currencyDivisor,
            $decimals,
            '.',
            ''
        );
    }
}
