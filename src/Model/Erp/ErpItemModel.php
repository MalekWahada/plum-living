<?php

declare(strict_types=1);

namespace App\Model\Erp;

use NetSuite\Classes\AssemblyItem;
use NetSuite\Classes\BooleanCustomFieldRef;
use NetSuite\Classes\CustomFieldRef;
use NetSuite\Classes\InventoryItem;
use NetSuite\Classes\ItemGroup;
use NetSuite\Classes\KitItem;
use NetSuite\Classes\ReadResponse;
use NetSuite\Classes\Record;
use NetSuite\Classes\Status;
use Webmozart\Assert\Assert;

/**
 * This class wraps the ReadResponse object returned by Netsuite lib
 * Class ErpItem
 * @package App\Model\Erp
 */
class ErpItemModel
{
    /**
     * @var AssemblyItem|InventoryItem|ItemGroup|KitItem|Record|null
     */
    private ?Record $item;

    /**
     * @var Status|null
     */
    private ?Status $status;

    /**
     * @var int|null
     */
    private ?int $id;

    /**
     * @var string|null
     */
    private ?string $type;

    /**
     * @var string|null
     */
    private ?string $code;

    /**
     * @var bool
     */
    private bool $isParent;

    /**
     * @var bool
     */
    private bool $isChild;

    /**
     * @var bool
     */
    private bool $isInactive;

    /**
     * @var bool
     */
    private bool $isOnline;

    /**
     * @var string|null
     */
    private ?string $displayName;

    /**
     * ErpItemModel constructor.
     * @param ReadResponse $item
     */
    public function __construct(ReadResponse $item)
    {
        Assert::notNull($item);
        $this->status = $item->status;
        $this->item = $item->record;

        /**
         * Get attributes from item
         */
        $this->id = isset($this->item->internalId) ? intval($this->item->internalId) : null; // Id
        $this->code = isset($this->item->itemId) ? strtoupper($this->item->itemId) : null; // Code
        $this->isInactive = $this->item->isInactive ?? false; // Inactive
        $this->isOnline = $this->item->isOnline ?? false; // Online
        $this->displayName = $this->item->displayName ?? $this->code;

        if (null !== $this->item) {
            /**
             * Type
             */
            switch (get_class($this->item)) {
                case InventoryItem::class:
                    $this->type = ErpItemType::INVENTORY_ITEM;
                    break;
                case AssemblyItem::class:
                    $this->type = ErpItemType::ASSEMBLY_ITEM;
                    break;
                case KitItem::class:
                    $this->type = ErpItemType::KIT_ITEM;
                    break;
                case ItemGroup::class:
                    $this->type = ErpItemType::ITEM_GROUP;
                    break;
                default:
                    $this->type = null;
            }

            /**
             * Each item can be a standalone item if it has no relations with another (not a child or parent)
             */
            if ($this->type === ErpItemType::INVENTORY_ITEM) { // Only inventory types have a field matrixType
                $this->isChild = isset($this->item->parent) && isset($this->item->matrixType) && $this->item->matrixType === "_child";
                $this->isParent = !$this->isChild && isset($this->item->matrixType) && $this->item->matrixType === "_parent";
            } elseif ($this->type === ErpItemType::KIT_ITEM || $this->type === ErpItemType::ITEM_GROUP) { // Kit and ItemGroup cannot have a matrix by default
                $this->isChild = isset($this->item->parent);
                $this->isParent = !$this->isChild && !isset($this->item->displayName); // Display name for parent kit must be null
            } else { // For Assembly we use another field
                $this->isChild = isset($this->item->parent);
                $this->isParent = !$this->isChild && isset($this->item->matrixItemNameTemplate);
            }
        }
    }

    /**
     * Get the type of the item
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * If item is valid
     * @return bool
     */
    public function isValid(): bool
    {
        return ($this->status->isSuccess ?? false)
            && null !== $this->id
            && null !== $this->code;
    }

    /**
     * Return if the item has a parent
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->isChild;
    }

    /**
     * Return if the item has no parent
     * @return bool
     */
    public function isParent(): bool
    {
        return $this->isParent;
    }

    /**
     * Return if the item is has no relations
     * @return bool
     */
    public function isStandalone(): bool
    {
        return !$this->isChild && !$this->isParent;
    }

    /**
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->isInactive;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * Verify if product must be kept or not.
     * Product is skipped by default unless availableForSale field is set to true
     * @return bool
     */
    public function isSkipped(): bool
    {
        // If product code is pre/suffixed by "DNU","TEST" or "CONS" + a dash, we delete/disable and skip this product
        if (str_starts_with($this->code, "DNU-") || str_ends_with($this->code, "-DNU")
            || str_starts_with($this->code, "TEST-") || str_ends_with($this->code, "-TEST")
            || str_starts_with($this->code, "CONS-") || str_ends_with($this->code, "-CONS")) {
            return true;
        }

        if (null !== $customFields = $this->getCustomFields()) {
            // Search for the available for sale field set to TRUE
            foreach ($customFields as $field) {
                if ($field->scriptId === ErpCustomField::AVAILABLE_FOR_SALE
                    && $field instanceof BooleanCustomFieldRef
                    && $field->value) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Return the internal id of the ERP item
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Item SKU
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Get the internal Netsuite item
     * @return AssemblyItem|InventoryItem|ItemGroup|KitItem|Record|null
     */
    public function getItem(): ?Record
    {
        return $this->item;
    }

    /**
     * Get the internal Netsuite status
     * @return Status|null
     */
    public function getStatus(): ?Status
    {
        return $this->status;
    }

    /**
     * Get the parent ERP id
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return isset($this->item->parent->internalId) ? intval($this->item->parent->internalId) : null;
    }

    /**
     * @return CustomFieldRef[]|null
     */
    public function getCustomFields(): ?array
    {
        return $this->item->customFieldList->customField ?? null;
    }
}
