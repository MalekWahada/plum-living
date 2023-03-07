<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\Context\IncorrectSwitchableTranslationException;
use App\Model\Translation\SwitchableTranslation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * This listener redirects if the current translation slug is not the right one.
 * Eg: /fr-fr will be redirected to /fr
 */
class IncorrectSwitchableTranslationExceptionListener
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!$exception instanceof IncorrectSwitchableTranslationException) {
            return;
        }

        $redirectParameters = [SwitchableTranslation::TRANSLATION_SLUG_PARAMETER => $exception->getShouldBeLocale()];

        // Get the original route from request
        if (null === $route = $request->attributes->get('_route')) {
            $response = new RedirectResponse($this->router->generate('sylius_shop_homepage', $redirectParameters));
        } else {
            $response = new RedirectResponse($this->router->generate($route, array_merge($request->attributes->get('_route_params', []), $redirectParameters)));
        }

        $event->setResponse($response);
    }
}
