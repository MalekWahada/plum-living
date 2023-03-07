<?php

declare(strict_types=1);

namespace App\Model\CustomerProject;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectItemPayloadModel extends ProjectItemOptionsPayloadModel
{
    private bool $isNewItem;
    private bool $isSavePayload;

    protected function getDataValidationConstraint(): ?Constraint
    {
        return new Assert\Collection([
            'fields' => [
                'itemId' => self::getConstraint($this->isNewItem, [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ]),
                'groupId' => self::getConstraint(!$this->isNewItem, [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ]),
                'quantity' => self::getConstraint(!$this->isSavePayload, [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'int']),
                    new Assert\Range(['min' => 1]),
                ]),
                'comment' => new Assert\Optional([
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255]),
                ]),
            ],
            'allowExtraFields' => true
        ]);
    }

    /**
     * @param array|string[] $data
     */
    public function __construct(array $data, string $payloadType = ProjectItemPayloadType::ITEM_DETAILS_PAYLOAD)
    {
        parent::__construct($data);
        $this->isNewItem = isset($data['groupId']);
        $this->isSavePayload = $payloadType === ProjectItemPayloadType::SAVE_PAYLOAD;
    }

    public function isNewItem(): bool
    {
        return $this->isNewItem;
    }

    public function getItemId(): ?string
    {
        return $this->data['itemId'] ?? null;
    }

    public function getGroupId(): ?string
    {
        return $this->data['groupId'] ?? null;
    }

    public function getQuantity(): ?int
    {
        return $this->data['quantity'] ?? null;
    }

    public function getComment(): ?string
    {
        return $this->data['comment'] ?? null;
    }
}
