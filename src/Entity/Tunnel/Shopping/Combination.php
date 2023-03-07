<?php

declare(strict_types=1);

namespace App\Entity\Tunnel\Shopping;

use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Resource\Model\ToggleableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="facade_combination")
 */
class Combination implements ResourceInterface, ImageAwareInterface
{
    use ToggleableTrait;

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
    protected $enabled = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $recommendation = null;

    public function getRecommendation(): ?string
    {
        return $this->recommendation;
    }

    public function setRecommendation(?string $recommendation): void
    {
        $this->recommendation = $recommendation;
    }

    /**
     * @ORM\OneToOne(targetEntity=CombinationImage::class, mappedBy="owner", orphanRemoval=true, cascade={"all"})
     */
    protected ?ImageInterface $image = null;

    /**
    * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
    * @ORM\JoinColumn(nullable=true, name="option_facade_color", referencedColumnName="id", nullable=true)
    */
    private ?ProductOptionValue $color = null;

    /**
    * @ORM\ManyToOne(targetEntity=Taxon::class)
    * @ORM\JoinColumn(nullable=false, name="facade_type", referencedColumnName="id", nullable=true)
    */
    private ?TaxonInterface $facadeType = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     * @ORM\JoinColumn(nullable=true, name="option_facade_design", referencedColumnName="id", nullable=true)
     */
    private ?ProductOptionValue $design = null;

    /**
    * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
    * @ORM\JoinColumn(nullable=true, name="option_facade_finish", referencedColumnName="id", nullable=true)
    */
    private ?ProductOptionValue $finish = null;

    /**
     * @var Collection|ProductOptionValue[]
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductOptionValue", mappedBy="combination", orphanRemoval=true, cascade={"all"})
     */
    protected Collection $linkedProductOptionsValues;

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(?ImageInterface $image): void
    {
        if (null === $image) {
            return;
        }

        $image->setOwner($this);
        $this->image = $image;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?ProductOptionValue
    {
        return $this->color;
    }

    public function setColor(?ProductOptionValue $color): void
    {
        $this->color = $color;
    }

    public function getFacadeType(): ?TaxonInterface
    {
        return $this->facadeType;
    }

    public function setFacadeType(?TaxonInterface $facadeType): void
    {
        $this->facadeType = $facadeType;
    }

    public function getDesign(): ?ProductOptionValue
    {
        return $this->design;
    }

    public function setDesign(?ProductOptionValue $design): void
    {
        $this->design = $design;
    }

    public function getFinish(): ?ProductOptionValue
    {
        return $this->finish;
    }

    public function setFinish(?ProductOptionValue $finish): void
    {
        $this->finish = $finish;
    }

    /**
     * @return Collection|ProductOptionValue[]
     */
    public function getLinkedProductOptionsValues(): Collection
    {
        return $this->linkedProductOptionsValues;
    }

    public function addLinkedProductOptionsValue(ProductOptionValue $linkedProductOptionsValue): self
    {
        if (!$this->linkedProductOptionsValues->contains($linkedProductOptionsValue)) {
            $this->linkedProductOptionsValues->add($linkedProductOptionsValue);
            $linkedProductOptionsValue->setCombination($this);
        }

        return $this;
    }

    public function removeLinkedProductOptionsValue(ProductOptionValue $linkedProductOptionsValue): self
    {
        if ($this->linkedProductOptionsValues->contains($linkedProductOptionsValue)) {
            $this->linkedProductOptionsValues->removeElement($linkedProductOptionsValue);
            if ($linkedProductOptionsValue->getCombination() === $this) {
                $linkedProductOptionsValue->setCombination(null);
            }
        }

        return $this;
    }
}
