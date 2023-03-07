<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Erp\Adapters\ProductAdapter;
use App\Model\Erp\ErpItemModel;
use App\Provider\Translation\TranslationLocaleProvider;

class ProductNameAdapter implements ProductAdapterInterface
{
    private TranslationLocaleProvider $localeProvider;

    public function __construct(TranslationLocaleProvider $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * ProductName is UNCOUPLED to the ERP but created if a new locale is added.
     * @param Product $product
     * @param ErpItemModel $erpItem
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        foreach ($this->localeProvider->getDefinedLocalesCodesOrDefault() as $localeCode) {
            $product->setFallbackLocale($localeCode); // Fallback locale must be set in order to create missing translations
            $translation = $product->getTranslation($localeCode);

            /**
             * Product name is 'découplé' == created but not updated. As name is a mandatory field, if a new locale is added, it must be created
             */
            if (false === ProductAdapter::FORCE_COUPLED && null !== $translation->getId()) { /** @phpstan-ignore-line */
                continue;
            }

            $translation->setName($erpItem->getDisplayName() ?? $erpItem->getCode());
        }
    }
}
