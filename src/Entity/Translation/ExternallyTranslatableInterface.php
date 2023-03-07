<?php

declare(strict_types=1);

namespace App\Entity\Translation;

interface ExternallyTranslatableInterface
{
    public function generateContentHash(?string $locale = null): ?string;

    public function getTranslationsPublishedAt(): ?\DateTime;

    public function setTranslationsPublishedAt(?\DateTime $translationsPublishedAt): void;

    public function getTranslationsPublishedContentHash(): ?string;

    public function setTranslationsPublishedContentHash(?string $translationsPublishedContentHash): void;
}
