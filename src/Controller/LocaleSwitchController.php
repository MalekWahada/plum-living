<?php

declare(strict_types=1);

namespace App\Controller;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\EventListener\RouterContextSwitchableTranslationSlugListener;
use App\Model\Translation\SwitchableTranslation;
use App\Provider\Translation\SwitchableTranslationProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocaleSwitchController extends AbstractController
{
    private SwitchableTranslationProvider $translationProvider;
    private UrlGeneratorInterface $urlGenerator;
    private SwitchableTranslationContextInterface $translationContext;

    public function __construct(
        SwitchableTranslationProvider $translationProvider,
        UrlGeneratorInterface $urlGenerator,
        SwitchableTranslationContextInterface $translationContext
    ) {
        $this->translationProvider = $translationProvider;
        $this->urlGenerator = $urlGenerator;
        $this->translationContext = $translationContext;
    }

    public function renderAction(): Response
    {
        return new Response(); // No rendering needed
    }

    public function switchAction(Request $request, ?string $code = null): Response
    {
        // Use previously known locale if no code is provided (case of access to /)
        if (null === $code) {
            $code = $this->translationContext->getSlug();
        }

        if (!array_key_exists($code, $this->translationProvider->getTranslations())) {
            throw new HttpException(
                Response::HTTP_NOT_ACCEPTABLE,
                sprintf('The slug code "%s" is invalid.', $code)
            );
        }

        return new RedirectResponse($this->urlGenerator->generate('sylius_shop_homepage', [SwitchableTranslation::TRANSLATION_SLUG_PARAMETER => $code]));
    }
}
