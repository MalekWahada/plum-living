<?php

declare(strict_types=1);

namespace App\Erp\Adapters\ProductVariant;

use App\Entity\Channel\ChannelPricing;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxation\TaxCategory;
use App\Model\Erp\ErpItemModel;
use NetSuite\Classes\Pricing;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

class ProductVariantPriceAdapter implements ProductVariantAdapterInterface
{
    public const ERP_VAT_RATE = 1.2;
    public const PRICE_PRECISION = 1000;
    public const ERP_CURRENCY = "Euro";
    public const ERP_PRICE_LEVEL = "Base Price";

    private FactoryInterface $channelPricingFactory;
    private LoggerInterface $erpImportLogger;
    private RepositoryInterface $taxCategoryRepository;

    public function __construct(
        FactoryInterface $channelPricingFactory,
        LoggerInterface $erpImportLogger,
        RepositoryInterface $taxCategoryRepository
    ) {
        $this->channelPricingFactory = $channelPricingFactory;
        $this->erpImportLogger = $erpImportLogger;
        $this->taxCategoryRepository = $taxCategoryRepository;
    }

    /**
     * Prices are COUPLED with the ERP
     * @param ProductVariant $productVariant
     * @param ErpItemModel $erpItem
     */
    public function adaptProductVariant(ProductVariant $productVariant, ErpItemModel $erpItem): void
    {
        if (!isset($erpItem->getItem()->pricingMatrix->pricing)) {
            $this->erpImportLogger->warning(sprintf("[PRODUCT-VARIANT][PRICE] No price (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
            return;
        }

        /** @var TaxCategoryInterface|null $defaultVatCategory */
        $defaultVatCategory = $this->taxCategoryRepository->findOneBy(['code' => TaxCategory::DEFAULT_TAX_CATEGORY_CODE]);

        $priceHT = null;
        foreach ($erpItem->getItem()->pricingMatrix->pricing as $priceHT) {
            /** @var Pricing $priceHT */
            if (isset($priceHT->priceLevel->name)
                && $priceHT->priceLevel->name === self::ERP_PRICE_LEVEL
                && $priceHT->currency->name === self::ERP_CURRENCY
                && count($priceHT->priceList->price) > 0) {
                $priceHT = $priceHT->priceList->price[0]->value;
                break;
            }
        }

        /** @var float|null $priceHT */
        if (null === $priceHT) {
            if ($productVariant->isEnabled()) {
                $this->erpImportLogger->warning(sprintf("[PRODUCT-VARIANT][PRICE] Not a valid price (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
            }
            $priceHT = 0;
        } elseif (0.0 === $priceHT) {
            if ($productVariant->isEnabled()) {
                $this->erpImportLogger->warning(sprintf("[PRODUCT-VARIANT][PRICE] Price is null (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
            }
        }

        $priceHT = $this->getCentimesRounded($priceHT);

        /** @var ProductInterface|null $product */
        $product = $productVariant->getProduct();

        if (null === $product) {
            $this->erpImportLogger->critical(sprintf("[PRODUCT-VARIANT][PRICE] Product is null (internalId=%s, code=%s)", $erpItem->getId() ?? '?', $erpItem->getCode()));
            return;
        }

        // Create or update the channel pricing
        /** @var ChannelInterface $channel */
        foreach ($product->getChannels() as $channel) {
            $channelPricing = $productVariant->getChannelPricingForChannel($channel);

            if (null === $channelPricing) {
                /** @var ChannelPricing $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channel->getCode());
                $channelPricing->setProductVariant($productVariant);
                $productVariant->addChannelPricing($channelPricing);
            }

            $channelPricing->setPrice($priceHT);
        }

        // Set VAT
        $productVariant->setTaxCategory($defaultVatCategory);
    }

    /**
     * Price working well with current tva 0.2 !
     * This method will try tro set HT price for a round TTC price with 0.05€ precision
     * @param float $price
     * @return int
     */
    private function getCentimesRounded(float $price): int
    {
        // Calc TTC price from centimes.
        $priceTTC = round($price * self::PRICE_PRECISION * self::ERP_VAT_RATE);
        // Round with 0.05 € precision (0.5 = it's a price * 2 rounded and divided by 2! and all with a factor of 10)
        $priceTTC = round($priceTTC * 10 * 2) / 10 / 2;
        // Return HT price of rounded TTC calc value
        return (int)round($priceTTC / self::ERP_VAT_RATE, 0);
    }
}
