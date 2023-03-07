<?php

declare(strict_types=1);

namespace App\Model\Translation;

use App\Exception\Translation\TranslationException;

interface TranslationErrorContainerInterface
{
    /**
     * @return array|TranslationException[]
     */
    public function getErrors(): array;

    public function addError(TranslationException $error): void;

    public function addErrors(array $errors): void;

    public function hasErrors(): bool;
}
