<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductTranslationInterface;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_group")
 * @UniqueEntity(
 *     fields={"code"},
 *     groups={"sylius", "Default"}
 * )
 */
class ProductGroup implements ResourceInterface, TranslatableInterface, CodeAwareInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->products = new ArrayCollection();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $code = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Taxonomy\Taxon")
     * @ORM\JoinColumn(name="main_taxon", referencedColumnName="id", nullable=false)
     * @Gedmo\SortableGroup
     */
    private TaxonInterface $mainTaxon;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition
     */
    private int $position = 0;

    /**
     * @var Collection|Product[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Product\Product", inversedBy="groups")
     * @ORM\JoinTable(name="product_group_products",
     *     joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")}
     *     )
     */
    private Collection $products;

    /**
     * @var Collection|AttributeValueInterface[]
     *
     */
    protected $attributes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getMainTaxon(): TaxonInterface
    {
        return $this->mainTaxon;
    }

    public function setMainTaxon(TaxonInterface $mainTaxon): void
    {
        $this->mainTaxon = $mainTaxon;
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
     * @return Product[]|Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $product): void
    {
        if ($this->products->contains($product)) {
            return;
        }

        $this->products->add($product);
        $product->addGroup($this);
    }

    public function removeProduct(Product $product): void
    {
        if (!$this->products->contains($product)) {
            return;
        }

        $this->products->removeElement($product);
        $product->removeGroup($this);
    }

    public function setName(?string $name): void
    {
        $this->getTranslation()->setName($name);
    }

    public function getName(): ?string
    {
        return $this->getTranslation()->getName();
    }

    public function getProductsAttributes(): array
    {
        $attributes = [];
        foreach ($this->getProducts() as $product) {
            foreach ($product->getAttributes() as $attribute) {
                if (!isset($attributes[$attribute->getCode()])) {
                    $attributes[$attribute->getCode()] = $attribute;
                }
            }
        }
        return $attributes;
    }

    public function getProductsOptions(): array
    {
        $options = [];
        foreach ($this->products as $product) {
            foreach ($product->getOptions() as $option) {
                if (!isset($options[$option->getCode()])) {
                    $options[$option->getCode()] = $option;
                }
            }
        }
        return $options;
    }

    public function getProductsTaxons(): array
    {
        $taxons = [];
        foreach ($this->products as $product) {
            foreach ($product->getTaxons() as $taxon) {
                if (!isset($taxons[$taxon->getCode()])) {
                    $taxons[$taxon->getCode()] = $taxon;
                }
            }
        }
        return $taxons;
    }

    public function hasProductsOptionCode(string $code): bool
    {
        foreach ($this->products as $product) {
            if ($product->getOptions()->exists(function (int $key, ProductOptionInterface $option) use ($code) {
                return $option->getCode() === $code;
            })) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslation(): TranslationInterface
    {
        return new ProductGroupTranslation();
    }

    /**
     * @return ProductTranslationInterface
     */
    public function getTranslation(?string $locale = null): TranslationInterface
    {
        /** @var ProductTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }
}
