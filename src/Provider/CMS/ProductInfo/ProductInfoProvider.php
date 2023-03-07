<?php

declare(strict_types=1);

namespace App\Provider\CMS\ProductInfo;

use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonTranslation;
use App\Formatter\CMS\TextToArrayFormatter;
use App\Repository\Taxon\TaxonRepository;
use Sylius\Component\Locale\Context\LocaleContextInterface;

class ProductInfoProvider
{
    private TaxonRepository $taxonRepository;
    private LocaleContextInterface $localeContext;

    public function __construct(TaxonRepository $taxonRepository, LocaleContextInterface $localeContext)
    {
        $this->taxonRepository = $taxonRepository;
        $this->localeContext = $localeContext;
    }

    /**
     * Extract a taxon product info content.
     *
     * @param string $code
     * @return string|null
     */
    public function getProductInfo(string $code): ?string
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $code]);
        if ($taxon !== null) {
            /** @var TaxonTranslation|null $taxonTranslation */
            $taxonTranslation = $taxon->getTranslation($this->localeContext->getLocaleCode());
            return $taxonTranslation->getProductInfo();
        }
        return null;
    }

    /**
     * Given a facade type we extract the accessory products info.
     * Accessory contents for PAX and METOD facade types are different.
     * in the B.O. we define the product infos for both facades under the same taxon (accessory).
     *
     * @param string $facadeTypeCode
     * @return string|null
     */
    public function getAccessoryProductInfo(string $facadeTypeCode): ?string
    {
        $content = $this->getProductInfo(Taxon::TAXON_ACCESSORY_CODE);
        if ($content !== null) {
            $formattedContent = TextToArrayFormatter::format($content);
            foreach ($formattedContent as $singleContent) {
                if ($this->evaluateAccessoryKeys($singleContent) && $singleContent['data']['facade_type'] === $facadeTypeCode) {
                    return $singleContent['data']['infos']['content'];
                }
            }
        }
        return null;
    }

    private function evaluateAccessoryKeys(array $array): bool
    {
        return isset($array['data']['facade_type'], $array['data']['infos']['content']);
    }
}
