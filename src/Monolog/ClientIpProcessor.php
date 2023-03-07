<?php

declare(strict_types=1);

namespace App\Monolog;

use Symfony\Component\HttpFoundation\RequestStack;

final class ClientIpProcessor
{
    private const CLOUDFLARE_CLIENT_IP_FIELD = 'cf-connecting-ip';

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(array $log): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$request->headers->has(self::CLOUDFLARE_CLIENT_IP_FIELD)) {
            return $log;
        }

        $log['extra']['client_ip'] = $request->headers->get(self::CLOUDFLARE_CLIENT_IP_FIELD);

        return $log;
    }
}
