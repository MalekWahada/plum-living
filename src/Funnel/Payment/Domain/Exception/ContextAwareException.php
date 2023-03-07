<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Domain\Exception;

abstract class ContextAwareException extends \Exception
{
    public const UNKNOWN_EXCEPTION_TYPE = 'unknown';

    private array $context;

    public function __construct(
        string $message = "",
        array $context = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getErrorType(): string
    {
        return $this->context['error_type'] ?? self::UNKNOWN_EXCEPTION_TYPE;
    }
}
