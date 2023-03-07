<?php

declare(strict_types=1);

namespace App\Factory\Page;

use App\Entity\Locale\Locale;
use App\Entity\Page\Page;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Yaml\Yaml;

final class ConfiguredPageFactory implements FactoryInterface
{
    private FactoryInterface $decoratedFactory;
    private RepositoryInterface $localeRepository;
    private string $templatesDirectory;

    public function __construct(
        FactoryInterface $decoratedFactory,
        RepositoryInterface $localeRepository,
        string $templatesDirectory
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->localeRepository = $localeRepository;
        $this->templatesDirectory = $templatesDirectory;
    }

    public function createNew(): Page
    {
        /** @var Page $page */
        $page = $this->decoratedFactory->createNew();

        return $page;
    }

    public function createProject(): Page
    {
        return $this->createTyped(Page::PAGE_TYPE_PROJECT);
    }

    public function createArticle(): Page
    {
        return $this->createTyped(Page::PAGE_TYPE_ARTICLE);
    }

    public function createMediaHome(): Page
    {
        return $this->createTyped(Page::PAGE_TYPE_MEDIA_HOME);
    }

    public function createMediaArticle(): Page
    {
        return $this->createTyped(Page::PAGE_TYPE_MEDIA_ARTICLE);
    }

    private function createTyped(string $type): Page
    {
        /** @var Locale[] $locales */
        $locales = $this->localeRepository->findAll();
        $pageProject = $this->createNew();

        $templateContent = Yaml::parseFile($this->templatesDirectory . $type . '.yaml');
        foreach ($locales as $locale) {
            $content = $templateContent ? json_encode($templateContent, JSON_THROW_ON_ERROR) : '{}';
            $pageProject->getTranslation($locale->getCode())->setContent($content);
        }
        $pageProject->setType($type);

        return $pageProject;
    }
}
