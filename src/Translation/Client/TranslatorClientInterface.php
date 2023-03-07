<?php

declare(strict_types=1);

namespace App\Translation\Client;

use App\Model\Translation\TranslationBagInterface;

interface TranslatorClientInterface
{
    public const CMS_PROJECT = 'cms';
    public const PRODUCT_COMPLETE_INFO_PROJECT = 'product_complete_info';
    public const TAXON_PROJECT = 'taxon';

    public function setProject(string $projectKey): void;
    public function publishKeys(TranslationBagInterface $bag): void;
    public function fetchAllKeys(TranslationBagInterface $bag, ?array $tagsFilter = null, ?array $keyNamesFilter = null, bool $translationsMustBeVerified = false): void;
}
