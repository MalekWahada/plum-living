<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use App\Entity\Product\ProductCompleteInfoTranslation;
use App\Entity\Translation\ExternallyTranslatableInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Taxon as BaseTaxon;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon")
 */
class Taxon extends BaseTaxon implements ExternallyTranslatableInterface
{
    public const TAXON_FACADE_CODE = 'facade';
    public const TAXON_FACADE_PAX = 'pax';
    public const TAXON_FACADE_METOD = 'metod';
    public const TAXON_FACADE_PAX_DOOR_CODE = 'pax_door';
    public const TAXON_FACADE_PAX_PANEL_CODE = 'pax_panel';
    public const TAXON_FACADE_METOD_DOOR_CODE = 'metod_door';
    public const TAXON_FACADE_METOD_DRAWER_CODE = 'metod_drawer';
    public const TAXON_FACADE_METOD_PANEL_CODE = 'metod_panel';
    public const TAXON_FACADE_METOD_BASEBOARD_CODE = 'metod_baseboard';
    public const TAXON_ACCESSORY_CODE = 'accessoires';
    public const TAXON_SAMPLE_CODE = 'echantillon';
    public const TAXON_SAMPLE_FRONT_CODE = 'echantillon_facade';
    public const TAXON_SAMPLE_PAINT_CODE = 'echantillon_peinture';
    public const TAXON_PAINT_CODE = 'peinture';
    public const TAXON_TAP_CODE = 'tap';
    public const TAXON_ACCESSORY_HANDLE_CODE = 'accessoires_handle';

    /**
     * Custom taxons for grouping products
     */
    public const CUSTOM_TAXON_PANEL_AND_PLINTH_CODE = 'panel_and_plinth';
    public const CUSTOM_TAXONS_PANEL_AND_PLINTH_CODES = [
        self::TAXON_FACADE_METOD_PANEL_CODE,
        self::TAXON_FACADE_PAX_PANEL_CODE,
        self::TAXON_FACADE_METOD_BASEBOARD_CODE
    ];
    public const CUSTOM_TAXON_DOOR_CODE = 'door';
    public const CUSTOM_TAXONS_DOOR_CODES = [
        self::TAXON_FACADE_METOD_DOOR_CODE,
        self::TAXON_FACADE_PAX_DOOR_CODE
    ];
    public const CUSTOM_TAXON_DRAWER_CODE = 'drawer';
    public const CUSTOM_TAXONS_DRAWER_CODES = [
        self::TAXON_FACADE_METOD_DRAWER_CODE
    ];

    /**
     * Flag to save if translations are already pushed to Lokalise
     * @ORM\Column(name="translations_published_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $translationsPublishedAt = null;

    /**
     * @ORM\Column(name="translations_published_content_hash", type="string", length=40, nullable=true)
     */
    protected ?string $translationsPublishedContentHash = null;

    protected function createTranslation(): TaxonTranslationInterface
    {
        return new TaxonTranslation();
    }

    public function isChildOf(string $taxonCode): bool
    {
        $parent = $this->getParent();
        while (null !== $parent) {
            if ($parent->getCode() === $taxonCode) {
                return true;
            }

            $parent = $parent->getParent();
        }

        return false;
    }

    public function getTranslationsPublishedAt(): ?\DateTime
    {
        return $this->translationsPublishedAt;
    }

    public function setTranslationsPublishedAt(?\DateTime $translationsPublishedAt): void
    {
        $this->translationsPublishedAt = $translationsPublishedAt;
    }

    public function getTranslationsPublishedContentHash(): ?string
    {
        return $this->translationsPublishedContentHash;
    }

    public function setTranslationsPublishedContentHash(?string $translationsPublishedContentHash): void
    {
        $this->translationsPublishedContentHash = $translationsPublishedContentHash;
    }

    public function generateContentHash(?string $locale = TaxonTranslation::PUBLISHED_LOCALE): ?string
    {
        /** @var TaxonTranslation $translation */
        $translation = $this->getTranslation($locale);
        $content = $translation->getProductInfo();
        return (null !== $content) ? sha1($content) : null;
    }
}
