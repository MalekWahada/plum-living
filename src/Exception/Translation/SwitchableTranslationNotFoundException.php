<?php

declare(strict_types=1);

namespace App\Exception\Translation;

class SwitchableTranslationNotFoundException extends \RuntimeException
{
    public function __construct(?string $message = null, \Exception $previousException = null)
    {
        parent::__construct($message ?: 'Translation slug could not be found!', 0, $previousException);
    }

    public static function notFound(string $slug): self
    {
        return new self(sprintf('Translation slug "%s" cannot be found!', $slug));
    }

    public static function notAvailable(string $slug, array $availableSlugs): self
    {
        return new self(sprintf(
            'Translation slug "%s" is not available! The available ones are: "%s".',
            $slug,
            implode('", "', $availableSlugs)
        ));
    }
}
