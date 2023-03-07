<?php

declare(strict_types=1);

namespace App\Provider\CMS\Link;

use App\Generator\Link\LinkGenerator;

class CMSLinkProvider
{
    private const ABSOLUTE_URL_REGEX = '/(?:^[a-z][a-z0-9+.-]*:|\/\/)/';
    private const NO_URL = '#';

    private LinkGenerator $linkGenerator;

    public function __construct(LinkGenerator $linkGenerator)
    {
        $this->linkGenerator = $linkGenerator;
    }

    public function generateAbsoluteURL(?string $url): string
    {
        if (null === $url) {
            return self::NO_URL;
        } elseif (preg_match(self::ABSOLUTE_URL_REGEX, $url)) {
            return $url;
        }

        return $this->linkGenerator->generateFromShopBaseUrl($url);
    }

    public function generateURLFromSlug(?string $slug): string
    {
        if (null === $slug) {
            return self::NO_URL;
        }
        $slug = $slug[0] === '/' ? substr($slug, 1) : $slug;

        return $this->linkGenerator->generateFromShopBaseUrl($slug);
    }
}
