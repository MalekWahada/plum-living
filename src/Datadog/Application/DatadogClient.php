<?php

declare(strict_types=1);

namespace App\Datadog\Application;

use DataDog\DogStatsd;

final class DatadogClient implements DatadogClientInterface
{
    private ?DogStatsd $datadog = null;

    public function __construct(
        string $datadogHost,
        int $datadogPort
    ) {
        if (!empty($datadogHost) && $datadogPort > 0) {
            $this->datadog = new DogStatsd([
                'host' => $datadogHost,
                'port' => $datadogPort,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function increment(string $metric, array $tags = []): void
    {
        if (null === $this->datadog) {
            return;
        }

        $this->datadog->increment($metric, 1.0, $tags);
    }
}
