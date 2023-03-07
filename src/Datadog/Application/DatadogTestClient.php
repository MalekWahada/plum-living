<?php

declare(strict_types=1);

namespace App\Datadog\Application;

final class DatadogTestClient implements DatadogClientInterface
{
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function increment(string $metric, array $tags = []): void
    {
    }
}
