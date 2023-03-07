<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Erp\ErpEntity;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Noksi\SyliusPlumHubspotPlugin\Traits\CrmEntityTrait;
use Noksi\SyliusPlumHubspotPlugin\Contract\CrmEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant", indexes={
 *     @ORM\Index(name="product_variant_enabled", columns={"enabled"})
 * })
 */
class ProductVariant extends BaseProductVariant implements CrmEntityInterface
{
    use CrmEntityTrait;
    public const DELIVERY_DATE_CALCULATION_MODE_DYNAMIC = "dynamic";
    public const DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_LACQUER = "fixed_date_range_lacquer";
    public const DELIVERY_DATE_CALCULATION_MODE_FIXED_RANGE_WOOD = "fixed_date_range_wood";

    /**
     * @ORM\Column(name="min_day_delivery", type="integer")
     */
    protected int $minDayDelivery = 0;

    /**
     * @ORM\Column(name="max_day_delivery", type="integer")
     */
    protected int $maxDayDelivery = 0;

    /**
     * @ORM\Column(name="delivery_calculation_mode", type="string", length=32, nullable=false, options={"default": self::DELIVERY_DATE_CALCULATION_MODE_DYNAMIC})
     */
    protected string $deliveryCalculationMode = self::DELIVERY_DATE_CALCULATION_MODE_DYNAMIC;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Erp\ErpEntity", orphanRemoval=true))
     * @ORM\JoinColumn(name="erp_entity_id")
     */
    protected ?ErpEntity $erpEntity = null;

    public function getMinDayDelivery(): int
    {
        return $this->minDayDelivery;
    }

    public function setMinDayDelivery(?int $minDayDelivery = null): void
    {
        if (null === $minDayDelivery) {
            $minDayDelivery = 0;
        }

        $this->minDayDelivery = $minDayDelivery;
    }

    public function getMaxDayDelivery(): int
    {
        return $this->maxDayDelivery;
    }

    public function setMaxDayDelivery(?int $maxDayDelivery = null): void
    {
        if (null === $maxDayDelivery) {
            $maxDayDelivery = 0;
        }

        $this->maxDayDelivery = $maxDayDelivery;
    }

    public function getDeliveryCalculationMode(): string
    {
        return $this->deliveryCalculationMode;
    }

    public function setDeliveryCalculationMode(?string $deliveryCalculationMode): void
    {
        if (null === $deliveryCalculationMode) {
            $deliveryCalculationMode = self::DELIVERY_DATE_CALCULATION_MODE_DYNAMIC; // Default mode
        }

        $this->deliveryCalculationMode = $deliveryCalculationMode;
    }

    public function getErpEntity(): ?ErpEntity
    {
        return $this->erpEntity;
    }

    public function setErpEntity(?ErpEntity $erpEntity): void
    {
        $this->erpEntity = $erpEntity;
    }

    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }

    public function getOptionValueCode(string $optionCode): ?string
    {
        /** @var ProductOptionValue $optionValue */
        foreach ($this->getOptionValues() as $optionValue) {
            if ($optionValue->getOption()->getCode() === $optionCode) {
                return $optionValue->getCode();
            }
        }
        return null;
    }

    public function getPrice(): ?int
    {
        /** @var ProductInterface $product */
        $product = $this->getProduct();
        $channels = $product->getChannels();

        /** @var ChannelInterface $channel */
        foreach ($channels as $channel) {
            $channelPricing = $this->getChannelPricingForChannel($channel);

            if (null === $channelPricing) {
                continue;
            }

            return $channelPricing->getPrice();
        }

        return null;
    }
}
