<?php

declare(strict_types=1);

namespace App\Model\Translation;

use App\Exception\Translation\TranslationException;

abstract class TranslationErrorContainer implements TranslationErrorContainerInterface
{
    /**
     * @var array|TranslationException[]
     */
    private array $errors = [];

    /**
     * @return array|TranslationException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(TranslationException $error): void
    {
        $this->errors[] = $error;
    }

    public function addErrors(array $errors): void
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
