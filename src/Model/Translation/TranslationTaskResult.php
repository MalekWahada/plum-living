<?php

declare(strict_types=1);

namespace App\Model\Translation;

class TranslationTaskResult extends TranslationErrorContainer
{
    private int $succeeded;
    private int $failed;

    public function __construct(int $succeeded = 0, int $failed = 0)
    {
        $this->succeeded = $succeeded;
        $this->failed = $failed;
    }
    public function getSucceeded(): int
    {
        return $this->succeeded;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function setSucceeded(int $succeeded): void
    {
        $this->succeeded = $succeeded;
    }

    public function setFailed(int $failed): void
    {
        $this->failed = $failed;
    }
}
