<?php

declare(strict_types=1);

namespace App\Model\CustomerProject;

use App\Entity\CustomerProject\ProjectItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ProjectItemOptionsPayloadModel extends AbstractProjectPayload implements ProjectPayloadInterface
{
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $availableOptionsConstraints = [];
        foreach (ProjectItem::AVAILABLE_OPTION_CODES as $optionCode) {
            $availableOptionsConstraints[$optionCode] = new Assert\Optional([
                new Assert\Type(['type' => 'string']),
            ]);
        }

        $metadata->addPropertyConstraint(
            'data',
            new Assert\Collection([
                'fields' => array_merge($availableOptionsConstraints, [
                    'variant' => new Assert\Optional([
                        new Assert\Type(['type' => 'string']),
                    ])
                ]),
                'allowExtraFields' => true
            ]),
        );
    }

    /**
     * @param array|string[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getVariant(): ?string
    {
        return $this->data['variant'] ?? null;
    }

    public function getOption(string $optionCode): ?string
    {
        if (!in_array($optionCode, ProjectItem::AVAILABLE_OPTION_CODES, true)) {
            return null;
        }

        return $this->data[$optionCode] ?? null;
    }
}
