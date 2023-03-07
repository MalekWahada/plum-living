<?php

declare(strict_types=1);

namespace App\Model\Translation;

class SwitchableTranslation
{
    public const TRANSLATION_SLUG_PARAMETER = '_trans_slug';

    public const SLUG_REGEX = '#^([A-Za-z]{2})(?>-([A-Za-z]{2}))?$#'; // Match nl-be
    public const URI_SLUG_REGEX = '#^\/?(([A-Za-z]{2})(?>-([A-Za-z]{2}))?)\/.*$#'; // Match /nl-be/anything
}
