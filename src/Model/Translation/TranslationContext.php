<?php

declare(strict_types=1);

namespace App\Model\Translation;

class TranslationContext
{
    private string $projectId;

    public function __construct(string $projectId)
    {
        $this->projectId = $projectId;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }
}
