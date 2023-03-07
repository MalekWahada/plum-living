<?php

declare(strict_types=1);

namespace App\Translation;

use App\Model\Translation\TranslationTaskResult;

interface TranslationTaskInterface
{
    public function bulkPublish(array $objects): TranslationTaskResult;

    public function bulkFetch(array $objects, string $locale): TranslationTaskResult;
}
