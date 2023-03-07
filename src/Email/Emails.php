<?php

declare(strict_types=1);

namespace App\Email;

class Emails
{
    public const EMAIL_SHARE_PLAN_CODE = 'share_plan';
    public const EMAIL_SHARE_CART_CODE = 'share_cart';
    public const EMAIL_API_ORDER_TOTAL_INCONSISTENCY_CODE = 'api_order_total_inconsistency';
    public const EMAIL_ORDER_ADJUSTMENTS_INCONSISTENCY_CODE = 'order_adjustments_inconsistency';
    public const EMAIL_ORDER_WIRE_PAYMENT_INFORMATION = 'wire_payment_information';
    public const EMAIL_ORDER_WIRE_PAYMENT_VALIDATED = 'wire_payment_validated';
    public const EMAIL_USER_REGISTRATION_B2B_PROGRAM = 'user_registration_b2b_program';
}
