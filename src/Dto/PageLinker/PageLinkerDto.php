<?php

declare(strict_types=1);

namespace App\Dto\PageLinker;

class PageLinkerDto
{
    protected string $code;

    protected string $title;

    public function __construct(
        string $code = '',
        string $title = ''
    ) {
        $this->code = $code;
        $this->title = $title;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
