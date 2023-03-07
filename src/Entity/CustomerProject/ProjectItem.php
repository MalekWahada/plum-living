<?php

declare(strict_types=1);

namespace App\Entity\CustomerProject;

use App\Entity\Channel\Channel;
use App\Entity\Product\Product;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="customer_project_item")
 */
class ProjectItem implements ResourceInterface
{
    public const AVAILABLE_OPTION_CODES = [
        ProductOption::PRODUCT_OPTION_DESIGN,
        ProductOption::PRODUCT_OPTION_FINISH,
        ProductOption::PRODUCT_OPTION_COLOR,
        ProductOption::PRODUCT_HANDLE_OPTION_FINISH,
        ProductOption::PRODUCT_TAP_OPTION_FINISH
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="ikea_sku", type="string", length=255, nullable=true)
     */
    protected ?string $ikeaSku = null;

    /**
     * @ORM\Column(name="ikea_quantity", type="integer", nullable=true)
     */
    protected ?int $ikeaQuantity = null;

    /**
     * @ORM\Column(name="ikea_unit_price", type="float", nullable=true)
     */
    protected ?float $ikeaUnitPrice = null;

    /**
     * @ORM\Column(name="ikea_total_price", type="float", nullable=true)
     */
    protected ?float $ikeaTotalPrice = null;

    /**
     * @ORM\Column(name="plum_label", type="string", length=255, nullable=true)
     */
    protected ?string $plumLabel = null;

    /**
     * @ORM\Column(name="cabinet_reference", type="string", length=255, nullable=true)
     */
    protected ?string $cabinetReference = null;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    protected ?string $currency = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $comment = null;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="items")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?Project $project = null;

    /**
     * @ORM\OneToOne(targetEntity=ProjectItemVariant::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(
     *     nullable=true,
     *     name="chosen_project_item_variant",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
    */
    protected ?ProjectItemVariant $chosenVariant = null;

    /**
     * @var Collection|ProjectItemVariant[]
     * @ORM\OneToMany(
     *     targetEntity=ProjectItemVariant::class,
     *     mappedBy="projectItem",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
    */
    protected Collection $variants;

    /**
     * Used to store temporary the group id for a new item for form model
     * @var string|null
     */
    private ?string $groupId = null;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getIkeaSku(): ?string
    {
        return $this->ikeaSku;
    }

    public function setIkeaSku(?string $ikeaSku): void
    {
        $this->ikeaSku = $ikeaSku;
    }

    public function getIkeaQuantity(): ?int
    {
        return $this->ikeaQuantity;
    }

    public function setIkeaQuantity(?int $ikeaQuantity): void
    {
        $this->ikeaQuantity = $ikeaQuantity;
    }

    public function getIkeaUnitPrice(): ?float
    {
        return $this->ikeaUnitPrice;
    }

    public function setIkeaUnitPrice(?float $ikeaUnitPrice): void
    {
        $this->ikeaUnitPrice = $ikeaUnitPrice;
    }

    public function getIkeaTotalPrice(): ?float
    {
        return $this->ikeaTotalPrice;
    }

    public function setIkeaTotalPrice(?float $ikeaTotalPrice): void
    {
        $this->ikeaTotalPrice = $ikeaTotalPrice;
    }

    public function getPlumLabel(): ?string
    {
        return $this->plumLabel;
    }

    public function setPlumLabel(?string $plumLabel): void
    {
        $this->plumLabel = $plumLabel;
    }

    public function getCabinetReference(): ?string
    {
        return $this->cabinetReference;
    }

    public function setCabinetReference(?string $cabinetReference): void
    {
        $this->cabinetReference = $cabinetReference;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getChosenVariant(): ?ProjectItemVariant
    {
        return $this->chosenVariant;
    }

    public function setChosenVariant(?ProjectItemVariant $chosenVariant): void
    {
        $this->chosenVariant = $chosenVariant;
    }

    /**
     * @return Collection|ProjectItemVariant[]
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(ProjectItemVariant $variant): void
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setProjectItem($this);
        }
    }

    public function removeVariant(ProjectItemVariant $variant): void
    {
        if ($this->variants->contains($variant)) {
            $this->variants->removeElement($variant);
        }
    }

    /**
     * Options are optional (could be null) if strict is false
     * This method filters the variants depending on options and strictness given
     * If an option is null and strict is false, the filter will not be selective
     * @param ProductOptionValueInterface|null $design
     * @param ProductOptionValueInterface|null $finish
     * @param ProductOptionValueInterface|null $color
     * @param ProductOptionValueInterface|null $handleFinish
     * @param ProductOptionValueInterface|null $tapFinish
     * @param bool $strict
     * @return ProjectItemVariant|null
     */
    public function getVariantByOptionValues(
        ?ProductOptionValueInterface $design,
        ?ProductOptionValueInterface $finish,
        ?ProductOptionValueInterface $color,
        ?ProductOptionValueInterface $handleFinish,
        ?ProductOptionValueInterface $tapFinish,
        bool                         $strict = true
    ): ?ProjectItemVariant {
        $matchedVariant = null;
        $hasDesign = $this->hasVariantsWithDesignOption();
        $hasFinish = $this->hasVariantsWithFinishOption();
        $hasColor = $this->hasVariantsWithColorOption();
        $hasHandleFinish = $this->hasVariantsWithHandleFinishOption();
        $hasTapFinish = $this->hasVariantsWithTapFinishOption();

        foreach ($this->getVariants() as $variant) {
            if ((isset($design) || isset($finish) || isset($color) || isset($handleFinish) || isset($tapFinish)) &&
                null !== $variant->getProductVariant()) {
                $optionValues = $variant->getProductVariant()->getOptionValues();
                // If strict => option must exist to be checked. If !strict, options must exist and not be null to be checked
                $containsDesign = (!$strict && null === $design) || !$hasDesign || $optionValues->contains($design);
                $containsFinish = (!$strict && null === $finish) || !$hasFinish || $optionValues->contains($finish);
                $containsColor = (!$strict && null === $color) || !$hasColor || $optionValues->contains($color);
                $containsHandleFinish = (!$strict && null === $handleFinish) || !$hasHandleFinish || $optionValues->contains($handleFinish);
                $containsTapFinish = (!$strict && null === $tapFinish) || !$hasTapFinish || $optionValues->contains($tapFinish);
                if ($containsDesign && $containsFinish && $containsColor && $containsHandleFinish && $containsTapFinish) {
                    $matchedVariant = $variant;
                    break;
                }
            }
        }

        return $matchedVariant;
    }

    /**
     * Check if project item has variants with given option values
     * Options are optional. If an option is null it will be skipped
     * Depending on given options, this method will return true if there
     * is one or more variants that has these option and false otherwise
     *
     * @param ProductOptionValueInterface|null $design
     * @param ProductOptionValueInterface|null $finish
     * @param ProductOptionValueInterface|null $color
     * @param ProductOptionValueInterface|null $handleFinish
     * @param ProductOptionValueInterface|null $tapFinish
     * @return bool
     */
    public function hasVariantsWithOptionValues(
        ?ProductOptionValueInterface $design,
        ?ProductOptionValueInterface $finish = null,
        ?ProductOptionValueInterface $color = null,
        ?ProductOptionValueInterface $handleFinish = null,
        ?ProductOptionValueInterface $tapFinish = null
    ): bool {
        return null !== $this->getVariantByOptionValues($design, $finish, $color, $handleFinish, $tapFinish, false);
    }

    public function hasAnyVariantsWithOption(): bool
    {
        return !$this->getVariantsOptionsFiltered()->isEmpty();
    }

    public function hasVariantsWithDesignOption(): bool
    {
        return $this->hasVariantsWithOptionCode(ProductOption::PRODUCT_OPTION_DESIGN);
    }

    public function hasVariantsWithFinishOption(): bool
    {
        return $this->hasVariantsWithOptionCode(ProductOption::PRODUCT_OPTION_FINISH);
    }

    public function hasVariantsWithColorOption(): bool
    {
        return $this->hasVariantsWithOptionCode(ProductOption::PRODUCT_OPTION_COLOR);
    }

    public function hasVariantsWithHandleFinishOption(): bool
    {
        return $this->hasVariantsWithOptionCode(ProductOption::PRODUCT_HANDLE_OPTION_FINISH);
    }

    public function hasVariantsWithTapFinishOption(): bool
    {
        return $this->hasVariantsWithOptionCode(ProductOption::PRODUCT_TAP_OPTION_FINISH);
    }

    /**
     * Return the list of all product options available for item variants
     * @return Collection|ProductOption[]
     */
    public function getVariantsOptionsFiltered(): Collection
    {
        $options = $this->getVariants()->map(function (ProjectItemVariant $variant) {
            return $variant->getOptions()->toArray();
        });
        return (new ArrayCollection(array_unique((array)array_merge(...$options))))->filter(function (ProductOption $option) {
            return in_array($option->getCode(), self::AVAILABLE_OPTION_CODES, true);
        });
    }

    /**
     * @return array|string[]
     */
    public function getVariantsOptionsCodesFiltered(): array
    {
        return array_values($this->getVariantsOptionsFiltered()->map(function (ProductOption $option) {
            return $option->getCode();
        })->toArray());
    }

    public function getUnitPrice(Channel $channel): int
    {
        $variant = $this->getChosenVariant()->getProductVariant();

        if (null !== $variant && $variant->hasChannelPricingForChannel($channel)) {
            return $variant->getChannelPricingForChannel($channel)->getPrice();
        }

        return 0;
    }

    public function getTotalPrice(Channel $channel): int
    {
        return $this->getUnitPrice($channel) * (null !== $this->getChosenVariant() ? $this->getChosenVariant()->getQuantity() : 0);
    }

    public function getVariantsTaxons(): Collection
    {
        $taxons = $this->variants->filter(function (ProjectItemVariant $variant) {
            return null !== $variant->getProductVariant();
        })->map(function (ProjectItemVariant $variant) {
            /** @var ProductInterface $product */
            $product = $variant->getProductVariant()->getProduct();
            return $product->getTaxons()->toArray();
        });
        return (new ArrayCollection(array_unique((array)array_merge(...$taxons))));
    }

    public function hasVariantsWithTaxonCode(string $taxonCode): bool
    {
        foreach ($this->variants as $variant) {
            if (null === $variant->getProductVariant() || null === $product = $variant->getProductVariant()->getProduct()) {
                continue;
            }

            /** @var Product $product */
            if ($product->isType($taxonCode)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns all available designs
     * @return array|ProductOptionValueInterface[]
     */
    public function getAvailableDesigns() : array
    {
        $designs = [];

        foreach ($this->getVariants() as $variant) {
            $design = $variant->getDesign();

            if (null !== $design && !in_array($design, $designs, true)) {
                $designs[] = $design;
            }
        }

        // sort by entity id
        usort($designs, static function ($design1, $design2) {
            return $design1->getId() > $design2->getId() ? 1 : 0;
        });

        return $designs;
    }

    /**
     * Returns all available finishes filtered with the input design
     * If no design is set, the list of all available finishes is returned
     * @param ProductOptionValueInterface|null $design
     * @return array|ProductOptionValueInterface[]
     */
    public function getAvailableFinishes(?ProductOptionValueInterface $design): array
    {
        $designs = $this->getAvailableDesigns(); // Get all designs
        $finishes = [];

        foreach ($this->getVariants() as $variant) {
            $finish = $variant->getFinish();

            // Filter design only if not null
            // Design must be a valid option of the project item to be filtered
            if (null !== $finish && !in_array($finish, $finishes, true)) {
                if ($design !== null && in_array($design, $designs, true) && $variant->getDesign() !== $design) {
                    continue;
                }
                $finishes[] = $finish;
            }
        }

        // sort by entity id
        usort($finishes, static function ($finish1, $finish2) {
            return $finish1->getId() > $finish2->getId() ? 1 : 0;
        });

        return $finishes;
    }

    /**
     * Returns all available colors filtered with the input design and finish
     * If no design or finish are set, the list of all available colors is returned
     * @param ProductOptionValueInterface|null $design
     * @param ProductOptionValueInterface|null $finish
     * @return array|ProductOptionValueInterface[]
     */
    public function getAvailableColors(
        ?ProductOptionValueInterface $design,
        ?ProductOptionValueInterface $finish
    ): array {
        $designs = $this->getAvailableDesigns(); // Get all designs
        $finishes = $this->getAvailableFinishes($design); // Get all finishes
        $colors = [];

        foreach ($this->getVariants() as $variant) {
            $color = $variant->getColor();

            // Filter design and finish only if not null
            // Design and finish must be a valid option of the project item to be filtered
            if (null !== $color &&
                !in_array($color, $colors, true)) {
                if (($design !== null && in_array($design, $designs, true) && $variant->getDesign() !== $design) ||
                    ($finish !== null && in_array($finish, $finishes, true) && $variant->getFinish() !== $finish)) {
                    continue;
                }
                $colors[] = $color;
            }
        }

        // sort by entity id
        usort($colors, static function ($color1, $color2) {
            return $color1->getId() > $color2->getId() ? 1 : 0;
        });

        return $colors;
    }

    /**
     * Returns all available finishes
     * @return array|ProductOptionValueInterface[]
     */
    public function getAvailableHandleFinishes() : array
    {
        $finishes = [];

        foreach ($this->getVariants() as $variant) {
            $finish = $variant->getHandleFinish();

            if (null !== $finish && !in_array($finish, $finishes, true)) {
                $finishes[] = $finish;
            }
        }

        // sort by entity id
        usort($finishes, static function ($finish1, $finish2) {
            return $finish1->getId() > $finish2->getId() ? 1 : 0;
        });

        return $finishes;
    }
    /**
     * Returns all available finishes
     * @return array|ProductOptionValueInterface[]
     */
    public function getAvailableTapFinishes() : array
    {
        $finishes = [];

        foreach ($this->getVariants() as $variant) {
            $finish = $variant->getTapFinish();

            if (null !== $finish && !in_array($finish, $finishes, true)) {
                $finishes[] = $finish;
            }
        }

        // sort by entity id
        usort($finishes, static function ($finish1, $finish2) {
            return $finish1->getId() > $finish2->getId() ? 1 : 0;
        });

        return $finishes;
    }

    /**
     * Returns true if a variant product contains a specific option code
     * @param string $code
     * @return bool
     */
    public function hasVariantsWithOptionCode(string $code): bool
    {
        if (!in_array($code, self::AVAILABLE_OPTION_CODES, true)) {
            return false;
        }

        foreach ($this->getVariants() as $variant) {
            if ($variant->getOptionsCodes()->contains($code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if in available options, there is more than one option value.
     * If there is no options available, checks if there is more than one variant.
     * @return bool
     */
    public function hasOptionsWithMultipleValues(): bool
    {
        if (!$this->hasAnyVariantsWithOption()) {
            return $this->variants->count() > 1;
        }

        foreach (self::AVAILABLE_OPTION_CODES as $optionCode) {
            if ($this->variants->filter(function (ProjectItemVariant $variant) use ($optionCode) {
                return $variant->getOptionsCodes()->contains($optionCode);
            })->count() > 1) {
                return true;
            }
        }
        return false;
    }
}
