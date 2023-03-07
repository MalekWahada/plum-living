<?php

declare(strict_types=1);

namespace App\Entity\Page;

use MonsieurBiz\SyliusCmsPagePlugin\Entity\PageInterface as BasePageInterface;

interface PageInterface extends BasePageInterface
{
    public function getReferenceLocaleCode(): string;

    public function setReferenceLocaleCode(string $referenceLocaleCode): void;
}
