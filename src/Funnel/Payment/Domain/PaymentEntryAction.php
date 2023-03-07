<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Domain;

final class PaymentEntryAction
{
    public const ACTION_REDIRECT = 'redirect';
    public const ACTION_RENDER = 'render';

    public string $action;

    public string $field;

    public array $options;

    public function __construct(
        string $action,
        string $field,
        array $options = []
    ) {
        $this->action = $action;
        $this->field = $field;
        $this->options = $options;
    }
}
