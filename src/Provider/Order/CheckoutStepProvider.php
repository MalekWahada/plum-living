<?php

declare(strict_types=1);

namespace App\Provider\Order;

use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutStepProvider
{
    private RequestStack $requestStack;

    public const STEP_ADDRESS = 1;
    public const STEP_SELECT_SHIPPING = 2;
    public const STEP_SELECT_PAYMENT = 3;
    public const STEP_SELECT_COMPLETE = 4;
    public const STEP_PAYMENT_SUCCESS = 5;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getCurrentStep(): int
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return self::STEP_ADDRESS;
        }

        switch ($request->get('_route')) {
            case 'sylius_shop_checkout_select_shipping':
                return self::STEP_SELECT_SHIPPING;
            case 'sylius_shop_checkout_select_payment':
            case 'sylius_shop_order_pay':
                return self::STEP_SELECT_PAYMENT;
            // the _summary template is used too after payment redirection to the thankYou page
            // and for a not paid order we need to have the STEP 4 template display conditions.
            case 'sylius_shop_order_show':
            case 'sylius_shop_checkout_complete':
                return self::STEP_SELECT_COMPLETE;
            case 'sylius_wire_payment_success':
            case 'sylius_shop_order_thank_you':
                return self::STEP_PAYMENT_SUCCESS;
            default:
                return self::STEP_ADDRESS;
        }
    }
}
