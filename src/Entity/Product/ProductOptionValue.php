<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Erp\ErpEntitiesAwareTrait;
use App\Entity\Erp\ErpEntity;
use App\Entity\Tunnel\Shopping\Combination;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ImagesAwareInterface;
use Sylius\Component\Product\Model\ProductOptionValue as BaseProductOptionValue;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_option_value")
 */
class ProductOptionValue extends BaseProductOptionValue implements ImagesAwareInterface
{
    /**
     * Finishes
     */
    public const FINISH_OAK_NATURAL_CODE = 'finish_chene_naturel';
    public const FINISH_WALNUT_NATURAL_CODE = 'finish_noyer_naturel';
    public const FINISH_LACQUER_MATT_CODE = 'finish_laque_mate';
    public const FINISH_OAK_PAINTED_CODE = 'finish_chene_peint';
    public const FINISH_BRASS_CODE = 'finish_laiton';

    public const FINISH_WITHOUT_SELECTED_COLORS = [
        self::FINISH_OAK_NATURAL_CODE,
        self::FINISH_WALNUT_NATURAL_CODE,
    ];

    /**
     * Colors
     */
    public const COLOR_NATURAL_CODE = 'color_naturel';
    public const COLOR_LAITON_BRASS_CODE = 'color_laiton_brass';
    public const COLOR_ON_DEMAND_CODE = 'color_sur_mesure';

    public const HIDDEN_COLORS = [
        self::COLOR_NATURAL_CODE,
        self::COLOR_LAITON_BRASS_CODE, // todo : must not be here it's a handle color . please add filter on option type !
        self::COLOR_ON_DEMAND_CODE,
    ];

    /**
     * Designs
     */
    public const DESIGN_UNIQUE_CODE = 'design_unique';
    public const DESIGN_FRAME_CODE = 'design_a_cadre';
    public const DESIGN_U_SHAPE_CODE = 'design_u_shape';
    public const DESIGN_STRAIGHT_CODE = 'design_lisse';
    public const DESIGN_CLASSIC_CANE_CODE = 'design_cannage_classique';
    public const DESIGN_ARCH_CANE_CODE = 'design_cannage_arche';

    use TranslatableTrait {
        TranslatableTrait::__construct as private initializeTranslationCollection;
        getTranslation as private doGetTranslation;
    }

    use ErpEntitiesAwareTrait {
        ErpEntitiesAwareTrait::__construct as private initializeErpEntitiesCollection;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected ?int $position = 0;

    /**
     * @ORM\Column(name="color_hex", type="string", length=10, nullable=true)
     */
    protected ?string $colorHex = null;

    /**
     * @var Combination|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Tunnel\Shopping\Combination", inversedBy="linkedProductOptionsValues")
     * @ORM\JoinColumn(nullable=true, name="combination_id", referencedColumnName="id")
     */
    protected ?Combination $combination = null;

    /**
     * @ORM\Column(name="combination_label", type="text", nullable=true)
     */
    protected ?string $combinationLabel = null;

    /**
     * @var Collection|ImageInterface[]
     * @ORM\OneToMany(targetEntity=ProductOptionValueImage::class, mappedBy="owner", orphanRemoval=true, cascade={"all"})
     */
    protected Collection $images;

    /**
     * @Groups({"product:read", "product_variant:read"})
     * @var Collection|ErpEntity[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Erp\ErpEntity")
     */
    protected Collection $erpEntities;

    public function __construct()
    {
        parent::__construct();

        $this->initializeTranslationCollection();
        $this->initializeErpEntitiesCollection();
        $this->images = new ArrayCollection();
    }

    protected function createTranslation(): ProductOptionValueTranslationInterface
    {
        return new ProductOptionValueTranslation();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getColorHex(): ?string
    {
        return $this->colorHex;
    }

    public function setColorHex(?string $colorHex): void
    {
        $this->colorHex = $colorHex;
    }

    public function getCombination(): ?Combination
    {
        return $this->combination;
    }

    public function setCombination(?Combination $combination): void
    {
        $this->combination = $combination;
    }

    public function getCombinationLabel(): ?string
    {
        return $this->combinationLabel;
    }

    public function setCombinationLabel(?string $combinationLabel): void
    {
        $this->combinationLabel = $combinationLabel;
    }

    public function getDescription(): ?string
    {
        /** @var ProductOptionValueTranslationInterface $translation */
        $translation = $this->getTranslation();
        return $translation->getDescription();
    }

    public function getTranslations(): Collection
    {
        if (null === $this->translations) {
            $this->initializeTranslationCollection();
        }

        return $this->translations;
    }

    /**
     * @return Collection|ImageInterface[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param string $type
     * @return Collection|ImageInterface[]
     */
    public function getImagesByType(string $type): Collection
    {
        return $this->images->filter(function (ImageInterface $image) use ($type) {
            return $type === $image->getType();
        });
    }

    public function hasImages(): bool
    {
        return !$this->images->isEmpty();
    }

    public function hasImage(ImageInterface $image): bool
    {
        return $this->images->contains($image);
    }

    public function addImage(ImageInterface $image): void
    {
        $image->setOwner($this);
        $this->images->add($image);
    }

    public function removeImage(ImageInterface $image): void
    {
        if ($this->hasImage($image)) {
            $image->setOwner(null);
            $this->images->removeElement($image);
        }
    }
}
