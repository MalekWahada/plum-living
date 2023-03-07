<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Product\ProductVariant;
use App\Erp\Adapters\ProductVariantAdapter;
use App\Model\Erp\ErpItemModel;
use App\Provider\Translation\TranslationLocaleProvider;

class ProductVariantNameAdapter implements ProductVariantAdapterInterface
{
    private TranslationLocaleProvider $localeProvider;

    public function __construct(TranslationLocaleProvider $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * Display name is UNCOUPLED to the ERP but created if a new locale is added.
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        foreach ($this->localeProvider->getDefinedLocalesCodesOrDefault() as $localeCode) {
            $productVariant->setFallbackLocale($localeCode); // Fallback locale must be set in order to create missing translations
            $translation = $productVariant->getTranslation($localeCode);

            /**
             * ProductVariant name 'découplé' == created but not updated. As name is a mandatory field, if a new locale is added, it must be created
             */
            if (false === ProductVariantAdapter::FORCE_COUPLED && null !== $translation->getId()) { /** @phpstan-ignore-line */
                continue;
            }

            $translation->setName($erpItem->getDisplayName() ?? $erpItem->getCode());
        }
    }
}
