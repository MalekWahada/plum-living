<?php

declare(strict_types=1);

namespace App\Model\CustomerProject;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectItemsDetailsPayloadModel extends AbstractProjectPayload
{
    /**
     * @var array|ProjectItemPayloadModel[]
     */
    private array $itemsData = [];

    protected function getDataValidationConstraint(): ?Constraint
    {
        return new Assert\Collection([
            'fields' => [
                'channelCode' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ],
                'itemsData' => [
                    new Assert\Type('array'),
                ]
            ],
            'allowExtraFields' => true
        ]);
    }

    public function __construct(array $data)
    {
        $this->data = $data;

        // Add items data
        if (isset($data['itemsData']) && is_array($data['itemsData'])) {
            foreach ($data['itemsData'] as $item) {
                $this->itemsData[] = new ProjectItemPayloadModel($item, ProjectItemPayloadType::ITEM_DETAILS_PAYLOAD);
            }
        }
    }

    public function validate(ValidatorInterface $validator, $constraints = null, $groups = null): ConstraintViolationListInterface
    {
        $violations = parent::validate($validator, $constraints, $groups);

        // Validate items
        foreach ($this->itemsData as $item) {
            $violations->addAll($item->validate($validator, $constraints, $groups));
        }

        return $violations;
    }

    /**
     * @return string|null
     */
    public function getChannelCode(): ?string
    {
        return $this->data['channelCode'] ?? null;
    }

    /**
     * @return array|ProjectItemPayloadModel[]|null
     */
    public function getItemsData(): ?array
    {
        return $this->itemsData;
    }
}
