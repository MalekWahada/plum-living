<?php

declare(strict_types=1);

namespace App\Model\CustomerProject;

use App\Entity\CustomerProject\ProjectItem;
use App\Entity\CustomerProject\ProjectItemVariant;
use App\Form\Type\CustomerProject\ProjectItemType;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

class ProjectItemFormModel
{
    private ?ProjectItem $internal = null;
    private ?int $id = null;
    private ?string $plumLabel = null;
    private ?string $cabinetReference = null;
    private ?string $groupId = null;
    private ?string $comment = null;
    private ?int $quantity = null;
    private ?ProductOptionValueInterface $design = null;
    private ?ProductOptionValueInterface $finish = null;
    private ?ProductOptionValueInterface $color = null;
    private ?ProductOptionValueInterface $handleFinish = null;
    private ?ProductOptionValueInterface $tapFinish = null;
    private ?ProjectItemVariant $variant = null;
    private ?string $productVariantId = null;

    public function init(ProjectItem $projectItem): void
    {
        $this->internal = $projectItem;
        $this->id = $projectItem->getId();
        $this->groupId = $projectItem->getGroupId();
        $this->plumLabel = $projectItem->getPlumLabel();
        $this->cabinetReference = $projectItem->getCabinetReference();
        $this->comment = $projectItem->getComment();
        if (null !== $projectItem->getChosenVariant()) {
            $this->variant = $projectItem->getChosenVariant();
            $this->design = $projectItem->getChosenVariant()->getDesign();
            $this->finish = $projectItem->getChosenVariant()->getFinish();
            $this->color = $projectItem->getChosenVariant()->getColor();
            $this->handleFinish = $projectItem->getChosenVariant()->getHandleFinish();
            $this->quantity = $projectItem->getChosenVariant()->getQuantity();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInternal(): ?ProjectItem
    {
        return $this->internal;
    }

    public function getPlumLabel(): ?string
    {
        return $this->plumLabel;
    }

    public function getCabinetReference(): ?string
    {
        return $this->cabinetReference;
    }

    public function setPlumLabel(?string $plumLabel): void
    {
        $this->plumLabel = $plumLabel;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $signId): void
    {
        $this->groupId = $signId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getDesign(): ?ProductOptionValueInterface
    {
        return $this->design;
    }

    public function setDesign(?ProductOptionValueInterface $design): void
    {
        $this->design = $design;
    }

    public function getFinish(): ?ProductOptionValueInterface
    {
        return $this->finish;
    }

    public function setFinish(?ProductOptionValueInterface $finish): void
    {
        $this->finish = $finish;
    }

    public function getColor(): ?ProductOptionValueInterface
    {
        return $this->color;
    }

    public function setColor(?ProductOptionValueInterface $color): void
    {
        $this->color = $color;
    }

    public function getHandleFinish(): ?ProductOptionValueInterface
    {
        return $this->handleFinish;
    }

    public function setHandleFinish(?ProductOptionValueInterface $handleFinish): void
    {
        $this->handleFinish = $handleFinish;
    }

    public function getTapFinish(): ?ProductOptionValueInterface
    {
        return $this->tapFinish;
    }

    public function setTapFinish(?ProductOptionValueInterface $tapFinish): void
    {
        $this->tapFinish = $tapFinish;
    }

    public function getVariant(): ?ProjectItemVariant
    {
        return $this->variant;
    }

    /**
     * Same field is used for new items and existing items.
     * @see ProjectItemType::buildForm()
     * @param ProjectItemVariant|string|null $variant
     */
    public function setVariant($variant): void
    {
        if ($variant instanceof ProjectItemVariant) {
            $this->variant = $variant;
        }
        if (is_string($variant)) {
            $this->productVariantId = $variant;
        }
    }

    public function getProductVariantId(): ?string
    {
        return $this->productVariantId;
    }
}
