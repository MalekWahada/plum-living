<?php

declare(strict_types=1);

namespace App\Authentication;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

final class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    private RouterInterface $router;

    public function __construct(
        HttpKernelInterface $httpKernel,
        HttpUtils $httpUtils,
        RouterInterface $router,
        array $options = [],
        LoggerInterface $logger = null
    ) {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
        $this->router = $router;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => $exception->getMessageKey()], 401);
        }

        // plumScannerLogin query parameter is added to sylius_shop_login_check route in order
        // to redirect to plum scanner login page rather than default login page
        if ($request->query->has('plumScannerLogin') ? (bool) $request->query->get('plumScannerLogin') : false) {
            $this->options['failure_path'] = $this->router->generate('app_plum_scanner_login', [
                'designCode' => $request->query->get('designCode'),
                'finishCode' => $request->query->get('finishCode'),
                'colorCode' => $request->query->get('colorCode')
            ]);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
