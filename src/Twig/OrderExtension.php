<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class OrderExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('get_account_status_label', [OrderRuntime::class, 'getAccountStatusLabel']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_current_cart_id', [OrderRuntime::class, 'getCurrentCartId']),
        ];
    }
}
