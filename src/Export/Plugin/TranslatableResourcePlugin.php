<?php

declare(strict_types=1);

namespace App\Export\Plugin;

use App\Provider\ImportExport\LocalizedFieldsProvider;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin as BaseResourcePlugin;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class TranslatableResourcePlugin extends BaseResourcePlugin
{
    private LocalizedFieldsProvider $fieldsProvider;

    public function __construct(RepositoryInterface $repository, PropertyAccessorInterface $propertyAccessor, EntityManagerInterface $entityManager, LocalizedFieldsProvider $fieldsProvider)
    {
        parent::__construct($repository, $propertyAccessor, $entityManager);
        $this->fieldsProvider = $fieldsProvider;
    }

    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        // Try to add automatically localized fields from translation resource
        foreach ($this->resources as $resource) {
            if (!$resource instanceof TranslatableInterface) {
                continue;
            }

            $fields = $this->entityManager->getClassMetadata(\get_class($resource->getTranslation()));

            foreach ($fields->getFieldNames() as $field) {
                $this->addDataForTranslatableResource($resource, $field);
            }
        }
    }

    /**
     * Add translations of a resource for all available locales
     * @param TranslatableInterface $resource
     * @param string $field The output field name
     * @param string|null $propertyName Internal property name of the entity (if different from the field name)
     * @param callable|null $valueTransformer Transformer for the value
     */
    protected function addDataForTranslatableResource(TranslatableInterface $resource, string $field, string $propertyName = null, callable $valueTransformer = null): void
    {
        if (null === $propertyName) {
            $propertyName = $field;
        }

        foreach ($this->fieldsProvider->generateLocalizedFieldName($field) as $locale => $localizedField) {
            $resource->setFallbackLocale($locale); // Set fallback locale. In case of missing translation, it will be created automatically.
            $translation = $resource->getTranslation($locale);
            $this->fieldNames[] = ucfirst($localizedField);

            if (!$this->propertyAccessor->isReadable($translation, $propertyName)) {
                continue;
            }

            if (is_callable($valueTransformer)) {
                $value = $valueTransformer($this->propertyAccessor->getValue($translation, $propertyName));
            } else {
                $value = $this->propertyAccessor->getValue($translation, $propertyName);
            }

            /** @var ResourceInterface $resource */
            $this->addDataForResource(
                $resource,
                ucfirst($localizedField),
                $value
            );
        }
    }
}
