<?php

declare(strict_types=1);

namespace App\Twig;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    private RepositoryInterface $localeRepository;
    private SwitchableTranslationProvider $switchableTranslationProvider;
    private SwitchableTranslationContextInterface $translationContext;

    public function __construct(RepositoryInterface $localeRepository, SwitchableTranslationProvider $switchableTranslationProvider, SwitchableTranslationContextInterface $translationContext)
    {
        $this->localeRepository = $localeRepository;
        $this->switchableTranslationProvider = $switchableTranslationProvider;
        $this->translationContext = $translationContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_locales', [$this, 'getLocales']),
            new TwigFunction('get_current_translation_slug', [$this, 'getCurrentTranslationSlug']),
            new TwigFunction('get_switchable_translations', [$this, 'getSwitchableTranslations']),
            new TwigFunction('get_switchable_translation', [$this, 'getSwitchableTranslation']),
        ];
    }

    public function getLocales(): array
    {
        return $this->localeRepository->findAll();
    }

    public function getCurrentTranslationSlug(): ?string
    {
        return $this->translationContext->getSlug();
    }

    public function getSwitchableTranslations(): array
    {
        return $this->switchableTranslationProvider->getTranslations();
    }

    public function getSwitchableTranslation(?string $slug): ?array
    {
        return $this->switchableTranslationProvider->getTranslation($slug);
    }
}
