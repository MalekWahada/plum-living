<?php

declare(strict_types=1);

namespace App\Provider\Translation;

use App\Exception\Context\RequestStackInvalidMasterRequest;
use App\Model\Translation\SwitchableTranslation;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RequestStackSwitchableTranslationSlugProvider
{
    private RequestStack $requestStack;
    private SessionInterface $session;

    public function __construct(RequestStack $requestStack, SessionInterface $session)
    {
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    /**
     * @throws RequestStackInvalidMasterRequest
     */
    public function getSlug(): ?string
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        if (null === $masterRequest) {
            throw new RequestStackInvalidMasterRequest('No master request available.');
        }

        // 1. Try from session as fallback
        $prevSlug = $this->session->get(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER);

        // 2. Try from request path as fallback
        preg_match(SwitchableTranslation::URI_SLUG_REGEX, $masterRequest->getPathInfo(), $matches);
        $slugFromPath = $matches[1] ?? $prevSlug;

        // 3. Try from request parameters (in case of CMS page and calling from RequestContext, request has no route parameter yet)
        return $masterRequest->attributes->get(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER, $slugFromPath);
    }
}
