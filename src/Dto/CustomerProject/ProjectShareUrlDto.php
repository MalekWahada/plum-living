<?php

declare(strict_types=1);

namespace App\Dto\CustomerProject;

class ProjectShareUrlDto
{
    public function __construct(?string $url = '')
    {
        $this->url = $url;
    }

    protected ?string $url;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
