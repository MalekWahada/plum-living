<?php

declare(strict_types=1);

namespace App\Generator\Link;

use Symfony\Component\Routing\RouterInterface;

class LinkGenerator
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function generateFromShopBaseUrl(string $url): string
    {
        return $this->router->generate('sylius_shop_homepage') . $url;
    }
}
