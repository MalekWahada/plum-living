<?php

declare(strict_types=1);

namespace App\Exception\Translation;

use Throwable;

class EmptyTranslationBagException extends TranslationException
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct('app.translation.empty_translation_bag', $code, $previous);
    }
}
