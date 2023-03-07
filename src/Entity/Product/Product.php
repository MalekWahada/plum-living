<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Erp\ErpEntity;
use App\Entity\Taxonomy\Taxon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product", indexes={
 *     @ORM\Index(name="product_enabled", columns={"enabled"})
 * })
 *
 * @method Taxon[]|Collection getTaxons()
 */
class Product extends BaseProduct
{
    public function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
    }

    /**
     * @Groups({"product:read", "order:read"})
     * @ORM\OneToOne(targetEntity="App\Entity\Erp\ErpEntity", orphanRemoval=true)
     * @ORM\JoinColumn(name="erp_entity_id")
     */
    protected ?ErpEntity $erpEntity;

    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Product\ProductCompleteInfo",
     *     mappedBy="product",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    protected ?ProductCompleteInfo $completeInfo = null;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private int $position = 0;

    /**
     * @var Collection|ProductGroup[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Product\ProductGroup", mappedBy="products", fetch="EXTRA_LAZY")
     */
    private Collection $groups;

    public function getErpEntity(): ?ErpEntity
    {
        return $this->erpEntity;
    }

    public function setErpEntity(?ErpEntity $erpEntity): void
    {
        $this->erpEntity = $erpEntity;
    }

    public function getCompleteInfo(): ?ProductCompleteInfo
    {
        return $this->completeInfo;
    }

    public function setCompleteInfo(?ProductCompleteInfo $completeInfo): void
    {
        if ($completeInfo === null && $this->completeInfo !== null) {
            $this->completeInfo->setProduct(null);
        }

        if ($completeInfo !== null && $completeInfo->getProduct() !== $this) {
            $completeInfo->setProduct($this);
        }

        $this->completeInfo = $completeInfo;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return ProductGroup[]|Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function addGroup(ProductGroup $group): void
    {
        if ($this->groups->contains($group)) {
            return;
        }

        $this->groups->add($group);
        $group->addProduct($this);
    }

    public function removeGroup(ProductGroup $group): void
    {
        if (!$this->groups->contains($group)) {
            return;
        }

        $this->groups->removeElement($group);
        $group->removeProduct($this);
    }

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }

    public function isFacade(): bool
    {
        return $this->isType(Taxon::TAXON_FACADE_CODE);
    }

    public function isFacadeSample(): bool
    {
        return $this->isType(Taxon::TAXON_SAMPLE_FRONT_CODE);
    }

    public function isPaint(): bool
    {
        return $this->isType(Taxon::TAXON_PAINT_CODE);
    }

    public function isPaintSample(): bool
    {
        return $this->isType(Taxon::TAXON_SAMPLE_PAINT_CODE);
    }

    /**
     * Determine if the product is type of $taxonCode (facade, sample, accessory ...)
     * @param string $taxonCode
     * @return bool
     */
    public function isType(string $taxonCode): bool
    {
        // TODO verify why certain products are without main TAXON : error reported in PK-378 !
        if (null === $this->getMainTaxon()) {
            return false;
        }

        if ($this->getMainTaxon()->getCode() === $taxonCode) {
            return true;
        }

        return !$this->getTaxons()->filter(function (Taxon $taxon) use ($taxonCode): bool {
            return $taxon->getCode() === $taxonCode || $taxon->isChildOf($taxonCode);
        })->isEmpty();
    }

    public function hasFacadeOptions(): bool
    {
        if ($this->getOptions()->isEmpty()) {
            return false;
        }
        $optionsCode = [];

        /** @var ProductOption $option */
        foreach ($this->getOptions() as $option) {
            $optionsCode[] = $option->getCode();
        }
        //make sure that the product has all the options in the "FACADE_SELECTED_OPTIONS"
        return count(array_intersect(ProductOption::FACADE_SELECTED_OPTIONS, $optionsCode)) === count(ProductOption::FACADE_SELECTED_OPTIONS);
    }

    public function hasOptionValue(string $optionValueCode): bool
    {
        if ($this->getVariants()->isEmpty()) {
            return false;
        }

        /** @var ProductVariant $variant */
        foreach ($this->getVariants() as $variant) {

            /** @var ProductOptionValue $optionValue */
            foreach ($variant->getOptionValues() as $optionValue) {
                if ($optionValue->getCode() === $optionValueCode) {
                    return true;
                }
            }
        }

        return false;
    }
}
