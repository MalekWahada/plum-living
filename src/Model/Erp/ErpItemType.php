<?php

declare(strict_types=1);

namespace App\Model\Erp;

abstract class ErpItemType
{
    const INVENTORY_ITEM = "InventoryItem";
    const ASSEMBLY_ITEM = "AssemblyItem";
    const KIT_ITEM = "KitItem";
    const ITEM_GROUP = "ItemGroup";
}
