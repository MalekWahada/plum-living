<?php

declare(strict_types=1);

namespace App\Twig;

use App\Provider\Order\CheckoutStepProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CheckoutStepExtension extends AbstractExtension
{
    private CheckoutStepProvider $checkoutStepProvider;

    public function __construct(CheckoutStepProvider $checkoutStepProvider)
    {
        $this->checkoutStepProvider = $checkoutStepProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('resolve_checkout_step', [$this, 'resolveCheckoutStep']),
        ];
    }

    public function resolveCheckoutStep(): int
    {
        return $this->checkoutStepProvider->getCurrentStep();
    }
}
