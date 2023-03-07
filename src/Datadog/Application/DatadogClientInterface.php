<?php

namespace App\Datadog\Application;

interface DatadogClientInterface
{
    /**
     * @param array<mixed> $tags
     */
    public function increment(string $metric, array $tags = []): void;
}
