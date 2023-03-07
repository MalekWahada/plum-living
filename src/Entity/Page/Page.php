<?php

declare(strict_types=1);

namespace App\Entity\Page;

use App\Entity\Translation\ExternallyTranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Locale\Locale;
use Doctrine\ORM\Mapping as ORM;
use MonsieurBiz\SyliusCmsPagePlugin\Entity\Page as BasePage;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="monsieurbiz_cms_page")
 */
class Page extends BasePage implements ImageAwareInterface, PageInterface, ExternallyTranslatableInterface
{
    public const PAGE_TYPE_DEFAULT = 'default';
    public const PAGE_TYPE_PROJECT = 'project';
    public const PAGE_TYPE_INSPIRATION = 'inspiration';
    public const PAGE_TYPE_ARTICLE = 'article';
    public const PAGE_TYPE_MEDIA_HOME = 'media_home';
    public const PAGE_TYPE_MEDIA_ARTICLE = 'media_article';
    public const PAGE_TYPE_HOME_PAGE = 'home_page';
    public const PAGE_TYPE_FRAME = 'frame';
    public const PAGE_TYPE_RIBBON = 'ribbon';

    public const PAGE_TYPE_PROJECT_PREFIX = 'home-projects';
    public const PAGE_TYPE_ARTICLE_PREFIX = 'journal';

    public const ALLOWED_PAGE_TYPES = [
        self::PAGE_TYPE_DEFAULT,
        self::PAGE_TYPE_PROJECT,
        self::PAGE_TYPE_INSPIRATION,
        self::PAGE_TYPE_ARTICLE,
        self::PAGE_TYPE_MEDIA_HOME,
        self::PAGE_TYPE_MEDIA_ARTICLE,
        self::PAGE_TYPE_HOME_PAGE,
        self::PAGE_TYPE_FRAME,
        self::PAGE_TYPE_RIBBON,
    ];

    public const NOT_SIMPLE_PAGE_TYPES = [
        self::PAGE_TYPE_ARTICLE,
        self::PAGE_TYPE_MEDIA_HOME,
        self::PAGE_TYPE_MEDIA_ARTICLE,
        self::PAGE_TYPE_PROJECT,
    ];

    /**
     * @ORM\Column(name="page_type", type="string", length=20)
     */
    protected ?string $type = self::PAGE_TYPE_DEFAULT;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Page\PageImage", mappedBy="owner", orphanRemoval=true, cascade={"all"})
     */
    protected ?ImageInterface $image = null;

    /**
     * @var Collection|PageTheme[]
     * @ORM\OneToMany(targetEntity=PageTheme::class, mappedBy="page", orphanRemoval=true, cascade={"all"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=false)
     */
    protected Collection $themes;

    /**
     * @ORM\Column(type="integer")
     */
    protected ?int $position = 0;

    /**
     * @ORM\Column(name="reference_locale_code", type="string", length=12, options={ "default": Locale::DEFAULT_LOCALE_CODE })
     */
    protected string $referenceLocaleCode = Locale::DEFAULT_LOCALE_CODE;

    /**
     * Flag to save if translations are already pushed to Lokalise
     * @ORM\Column(name="translations_published_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $translationsPublishedAt = null;

    /**
     * @ORM\Column(name="translations_published_content_hash", type="string", length=40, nullable=true)
     */
    protected ?string $translationsPublishedContentHash = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $category = null;

    /**
     * @var Collection|PageOption[]
     * @ORM\OneToMany(targetEntity=PageOption::class, mappedBy="page", orphanRemoval=true, cascade={"all"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=false)
     */
    protected Collection $options;

    public function __construct()
    {
        parent::__construct();
        $this->themes = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        if (in_array($type, self::ALLOWED_PAGE_TYPES, true)) {
            $this->type = $type;
        } else {
            $this->type = self::PAGE_TYPE_DEFAULT;
        }
    }

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(?ImageInterface $image): void
    {
        if (null !== $image) {
            $image->setOwner($this);
        }
        $this->image = $image;
    }

    /**
     * @return Collection|PageTheme[]
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function hasTheme(PageTheme $theme): bool
    {
        return $this->themes->contains($theme);
    }

    public function addTheme(PageTheme $theme): void
    {
        if ($this->hasTheme($theme)) {
            return;
        }
        $theme->setPage($this);
        $this->themes->add($theme);
    }

    public function removeTheme(PageTheme $theme): void
    {
        $this->themes->removeElement($theme);
    }

    /**
     * @return Collection|PageOption[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function hasOption(PageOption $option): bool
    {
        return $this->options->contains($option);
    }

    public function addOption(PageOption $option): void
    {
        if ($this->hasOption($option)) {
            return;
        }
        $option->setPage($this);
        $this->options->add($option);
    }

    public function removeOption(PageOption $option): void
    {
        $this->options->removeElement($option);
    }

    public function getOptionByType(string $type): ?PageOption
    {
        $options = $this->getOptions()->filter(
            fn (PageOption $option) => $option->getType() === $type
        );

        return $options->count() ? $options->first() : null;
    }

    public function hasOptionByType(string $type): bool
    {
        return $this->getOptionByType($type) instanceof PageOption;
    }

    public function setOptionByType(string $type, string $value): void
    {
        $option = $this->getOptionByType($type);
        if (! $option) {
            $option = new PageOption();
            $option->setPage($this);
            $option->setType($type);
            $option->setValue($value);
            $this->options->add($option);
            return;
        }
        $option->setValue($value);
    }

    public function getColor(): ?string
    {
        $value = $this->getOptionByType('color');
        return $value ? $value->getValue() : null;
    }

    public function setColor(?string $color): void
    {
        if (! $color) {
            return;
        }
        $this->setOptionByType('color', $color);
    }

    public function getRoom(): ?string
    {
        $value = $this->getOptionByType('room');
        return $value ? $value->getValue() : null;
    }

    public function setRoom(?string $room): void
    {
        if (! $room) {
            return;
        }
        $this->setOptionByType('room', $room);
    }

    public function getBudget(): ?int
    {
        $value = $this->getOptionByType('budget');
        return $value ? (int) $value->getValue() : null;
    }

    public function setBudget(?string $budget): void
    {
        if (! $budget) {
            return;
        }
        $this->setOptionByType('budget', $budget);
    }

    public static function getAllowedSimplePageTypes(): array
    {
        return array_diff(
            self::ALLOWED_PAGE_TYPES,
            self::NOT_SIMPLE_PAGE_TYPES
        );
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position ?? 0;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getReferenceLocaleCode(): string
    {
        return $this->referenceLocaleCode;
    }

    public function setReferenceLocaleCode(string $referenceLocaleCode): void
    {
        $this->referenceLocaleCode = $referenceLocaleCode;
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

    public function generateContentHash(?string $locale = null): ?string
    {
        $content = $this->getTranslation($locale)->getContent();
        return (null !== $content) ? sha1($content) : null;
    }
}
