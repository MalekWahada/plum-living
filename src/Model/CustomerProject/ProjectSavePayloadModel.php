<?php

declare(strict_types=1);

namespace App\Model\CustomerProject;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectSavePayloadModel extends AbstractProjectPayload
{
    /**
     * @var array|ProjectItemPayloadModel[]
     */
    private array $newItems = [];

    /**
     * @var array|ProjectItemPayloadModel[]
     */
    private array $updatedItems = [];

    protected function getDataValidationConstraint(): ?Constraint
    {
        return new Assert\Collection([
            'fields' => [
                'name' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255])
                ]),
                'comment' => new Assert\Optional([
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255])
                ]),
                'globalOptions' => new Assert\Optional(
                    new Assert\Collection([
                        'design' => new Assert\Optional([
                            new Assert\Type(['type' => 'string']),
                        ]),
                        'finish' => new Assert\Optional([
                            new Assert\Type(['type' => 'string']),
                        ]),
                        'color' => new Assert\Optional([
                            new Assert\Type(['type' => 'string']),
                        ]),
                    ])
                ),
                'newItems' => new Assert\Optional([
                    new Assert\Type('array'),
                ]),
                'updatedItems' => new Assert\Optional([
                    new Assert\Type('array'),
                ]),
                'removedItems' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'string']),
                    ]),
                ])
            ],
            'allowExtraFields' => true
        ]);
    }

    public function __construct(array $data)
    {
        $this->data = $data;

        // Add new items
        if (isset($data['newItems']) && is_array($data['newItems'])) {
            foreach ($data['newItems'] as $item) {
                $this->newItems[] = new ProjectItemPayloadModel($item, ProjectItemPayloadType::SAVE_PAYLOAD);
            }
        }

        // Add updated items
        if (isset($data['updatedItems']) && is_array($data['updatedItems'])) {
            foreach ($data['updatedItems'] as $item) {
                $this->updatedItems[] = new ProjectItemPayloadModel($item, ProjectItemPayloadType::SAVE_PAYLOAD);
            }
        }
    }

    public function validate(ValidatorInterface $validator, $constraints = null, $groups = null): ConstraintViolationListInterface
    {
        $violations = parent::validate($validator, $constraints, $groups);

        // Validate new items
        foreach ($this->newItems as $item) {
            $violations->addAll($item->validate($validator, $constraints, $groups));
        }

        // Validate updated items
        foreach ($this->updatedItems as $item) {
            $violations->addAll($item->validate($validator, $constraints, $groups));
        }

        return $violations;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function getComment(): ?string
    {
        return $this->data['comment'] ?? null;
    }

    /**
     * @return array|string[]|null
     */
    public function getGlobalOptions(): ?array
    {
        return $this->data['globalOptions'] ?? null;
    }

    /**
     * @return array|ProjectItemPayloadModel[]|null
     */
    public function getNewItems(): ?array
    {
        return $this->newItems;
    }

    /**
     * @return array|ProjectItemPayloadModel[]|null
     */
    public function getUpdatedItems(): ?array
    {
        return $this->updatedItems;
    }

    /**
     * @return array|string[]|null
     */
    public function getRemovedItems(): ?array
    {
        return $this->data['removedItems'] ?? null;
    }
}
