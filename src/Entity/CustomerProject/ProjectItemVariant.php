<?php

declare(strict_types=1);

namespace App\Entity\CustomerProject;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductVariant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="customer_project_item_variant")
 */
class ProjectItemVariant implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProjectItem::class, inversedBy="variants")
     * @ORM\JoinColumn(name="project_item_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?ProjectItem $projectItem = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductVariant::class)
     * @ORM\JoinColumn(nullable=false, name="product_variant_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?ProductVariantInterface $productVariant = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $quantity;

    /**
     * @var array|ProductOptionValueInterface[]
     */
    protected array $optionValues = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjectItem(): ?ProjectItem
    {
        return $this->projectItem;
    }

    public function setProjectItem(?ProjectItem $projectItem): void
    {
        $this->projectItem = $projectItem;
    }

    public function getProductVariant(): ?ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        if (null === $quantity) {
            $quantity = 0;
        }
        $this->quantity = $quantity;
    }

    public function getDesign(): ?ProductOptionValueInterface
    {
        return $this->getOptionValue(ProductOption::PRODUCT_OPTION_DESIGN);
    }

    public function getFinish(): ?ProductOptionValueInterface
    {
        return $this->getOptionValue(ProductOption::PRODUCT_OPTION_FINISH);
    }

    public function getColor(): ?ProductOptionValueInterface
    {
        return $this->getOptionValue(ProductOption::PRODUCT_OPTION_COLOR);
    }

    public function getHandleFinish(): ?ProductOptionValueInterface
    {
        return $this->getOptionValue(ProductOption::PRODUCT_HANDLE_OPTION_FINISH);
    }

    public function getTapFinish(): ?ProductOptionValueInterface
    {
        return $this->getOptionValue(ProductOption::PRODUCT_TAP_OPTION_FINISH);
    }

    /**
     * @return Collection|ProductOptionInterface[]
     */
    public function getOptions(): Collection
    {
        if ((null !== $productVariant = $this->getProductVariant()) && (null !== $product = $productVariant->getProduct())) {
            return $product->getOptions();
        }
        return new ArrayCollection();
    }

    /**
     * @return Collection|string[]
     */
    public function getOptionsCodes(): Collection
    {
        /** @phpstan-ignore-next-line */
        return $this->getOptions()->filter(function (ProductOption $option) {
            return $option->getCode() !== null;
        })->map(function (ProductOption $option) {
            return $option->getCode();
        });
    }

    public function getOptionValue(string $optionCode): ?ProductOptionValueInterface
    {
        if (!in_array($optionCode, ProjectItem::AVAILABLE_OPTION_CODES, true)) {
            return null;
        }

        if (!isset($this->optionValues[$optionCode])) {
            if (null === $this->getProductVariant()) {
                return null;
            }
            $optionValue = $this->getProductVariant()->getOptionValues()->filter(
                function (ProductOptionValueInterface $optionValue) use ($optionCode) {
                    return $optionValue->getOption()->getCode() === $optionCode;
                }
            );

            $this->optionValues[$optionCode] = false !== $optionValue->first() ? $optionValue->first() : null;
        }
        return $this->optionValues[$optionCode];
    }
}
