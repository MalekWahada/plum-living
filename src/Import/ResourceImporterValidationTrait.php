<?php

declare(strict_types=1);

namespace App\Import;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ResourceImporterValidationTrait
{
    private ValidatorInterface $validator;

    /**
     * @param ResourceInterface $resource
     * @param array|string[] $groups
     * @throws ItemIncompleteException
     */
    public function validate(ResourceInterface $resource, array $groups = ['Default', 'sylius']): void
    {
        $errors = $this->validator->validate($resource, null, $groups);
        if (count($errors) > 0) {
            $message = sprintf('Validation error for "%s": ', method_exists($resource, 'getCode') ? $resource->getCode() : $resource->getId());
            foreach ($errors as $error) {
                $message .= '[' . $error->getPropertyPath() . '] ' . $error->getMessage() . '; ';
            }
            throw new ItemIncompleteException($message);
        }
    }
}
