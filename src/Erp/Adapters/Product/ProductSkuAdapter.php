<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Erp\Slugifier;
use App\Model\Erp\ErpItemModel;
use App\Provider\Translation\TranslationLocaleProvider;

class ProductSkuAdapter implements ProductAdapterInterface
{
    private TranslationLocaleProvider $localeProvider;

    public function __construct(TranslationLocaleProvider $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * SKU is COUPLED to the ERP
     * @param Product $product
     * @param ErpItemModel $erpItem
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        $product->setCode(Slugifier::slugifyCode($erpItem->getCode()));

        foreach ($this->localeProvider->getDefinedLocalesCodesOrDefault() as $localeCode) {
            $product->setFallbackLocale($localeCode); // Fallback locale must be set in order to create missing translations
            $product->getTranslation($localeCode)->setSlug(Slugifier::slugifyCode($erpItem->getCode()));
        }
    }
}
