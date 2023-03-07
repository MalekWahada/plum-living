<?php

namespace App\EventListener;

use App\Context\RequestBasedChannelResolver;
use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Model\Translation\SwitchableTranslation;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContextAwareInterface;

/**
 * Initializes the translatable slug based on the current request (channels countries and locales).
 * Slug is set in router and is used to generate URLs.
 * @see RequestBasedChannelResolver
 */
class RouterContextSwitchableTranslationSlugListener
{
    private SwitchableTranslationContextInterface $translationContext;
    private ?RequestContextAwareInterface $router;

    public function __construct(SwitchableTranslationContextInterface $translationContext, RequestContextAwareInterface $router = null)
    {
        $this->translationContext = $translationContext;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (null !== $this->router) {
            $this->router->getContext()->setParameter(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER, $this->translationContext->getSlug());
        }
    }
}
