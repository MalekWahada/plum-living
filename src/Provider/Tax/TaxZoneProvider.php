<?php

declare(strict_types=1);

namespace App\Provider\Tax;

use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Scope;

class TaxZoneProvider
{
    private ZoneMatcherInterface $zoneMatcher;

    public function __construct(ZoneMatcherInterface $zoneMatcher)
    {
        $this->zoneMatcher = $zoneMatcher;
    }

    public function getTaxZone(OrderInterface $order): ?ZoneInterface
    {
        $billingAddress = $order->getBillingAddress();
        $zone = null;

        if (null !== $billingAddress) {
            $zone = $this->zoneMatcher->match($billingAddress, Scope::TAX);
        }

        return $zone ?: $order->getChannel()->getDefaultTaxZone();
    }
}
