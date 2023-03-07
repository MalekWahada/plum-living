<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Translation\ExternallyTranslatableInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="plum_product_complete_info")
 */
class ProductCompleteInfo implements ResourceInterface, TranslatableInterface, ExternallyTranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $enabled = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Product\Product", inversedBy="completeInfo")
     * @ORM\JoinColumn(name="product_id", nullable=false, referencedColumnName="id")
     */
    private ?Product $product = null;

    /**
     * Flag to save if translations are already pushed to Lokalise
     * @ORM\Column(name="translations_published_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $translationsPublishedAt = null;

    /**
     * @ORM\Column(name="translations_published_content_hash", type="string", length=40, nullable=true)
     */
    protected ?string $translationsPublishedContentHash = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): void
    {
        $this->product = $product;
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

    protected function createTranslation(): ProductCompleteInfoTranslation
    {
        return new ProductCompleteInfoTranslation();
    }

    public function generateContentHash(?string $locale = ProductCompleteInfoTranslation::PUBLISHED_LOCALE): ?string
    {
        /** @var ProductCompleteInfoTranslation $translation */
        $translation = $this->getTranslation($locale);
        $content = $translation->getContent();
        return (null !== $content) ? sha1($content) : null;
    }
}
