<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Domain\Exception;

use Exception;
use Throwable;

final class UnsupportedPaymentMethodException extends Exception
{
    public function __construct(string $message = "Payment Method is not supported", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
