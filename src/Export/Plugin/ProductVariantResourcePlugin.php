<?php

declare(strict_types=1);

namespace App\Export\Plugin;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductVariant;
use App\Formatter\Money\MoneyFormatter;
use App\Provider\ImportExport\LocalizedFieldsProvider;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function rtrim;

final class ProductVariantResourcePlugin extends TranslatableResourcePlugin
{
    private MoneyFormatter $moneyFormatter;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        MoneyFormatter $moneyFormatter,
        LocalizedFieldsProvider $provider
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager, $provider);
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var ProductVariant $resource */
        foreach ($this->resources as $resource) {
            $this->addTranslationData($resource);
            $this->addProductData($resource);
            $this->addOptionData($resource);
            $this->addChannelData($resource);
            $this->addPriceData($resource);
            $this->addDeliveryData($resource);
        }
    }

    private function addProductData(ProductVariantInterface $resource): void
    {
        /** @var ProductInterface $product */
        $product = $resource->getProduct();

        $this->addDataForResource($resource, 'ProductCode', $product->getCode());
    }

    private function addTranslationData(ProductVariantInterface $resource): void
    {
        $translation = $resource->getTranslation();

        $this->addDataForResource($resource, 'Name', $translation->getName());
    }

    private function addOptionData(ProductVariantInterface $resource): void
    {
        foreach ($resource->getOptionValues() as $optionValue) {
            switch ($optionValue->getOption()->getCode()) {
                case ProductOption::PRODUCT_OPTION_FINISH:
                    $this->addDataForResource(
                        $resource,
                        ucfirst(ProductOption::PRODUCT_OPTION_FINISH),
                        $optionValue->getCode()
                    );
                    break;
                case ProductOption::PRODUCT_OPTION_DESIGN:
                    $this->addDataForResource(
                        $resource,
                        ucfirst(ProductOption::PRODUCT_OPTION_DESIGN),
                        $optionValue->getCode()
                    );
                    break;
                case ProductOption::PRODUCT_OPTION_COLOR:
                    $this->addDataForResource(
                        $resource,
                        ucfirst(ProductOption::PRODUCT_OPTION_COLOR),
                        $optionValue->getCode()
                    );
                    break;
                case ProductOption::PRODUCT_HANDLE_OPTION_DESIGN:
                    $this->addDataForResource(
                        $resource,
                        'DesignHandle', // Custom option name to avoid underscore in column
                        $optionValue->getCode()
                    );
                    break;
                case ProductOption::PRODUCT_HANDLE_OPTION_FINISH:
                    $this->addDataForResource(
                        $resource,
                        'FinishHandle',
                        $optionValue->getCode()
                    );
                    break;
                case ProductOption::PRODUCT_TAP_OPTION_DESIGN:
                    $this->addDataForResource(
                        $resource,
                        'DesignTap',
                        $optionValue->getCode()
                    );
                    break;
                case ProductOption::PRODUCT_TAP_OPTION_FINISH:
                    $this->addDataForResource(
                        $resource,
                        'FinishTap',
                        $optionValue->getCode()
                    );
                    break;
                default:
                    break;
            }
        }
    }

    private function addChannelData(ProductVariantInterface $resource): void
    {
        $channelSlug = '';

        /** @var ProductInterface $product */
        $product = $resource->getProduct();

        /** @var ChannelInterface[] $channels */
        $channels = $product->getChannels();
        foreach ($channels as $channel) {
            $channelSlug .= $channel->getCode() . '|';
        }

        $channelSlug = rtrim($channelSlug, '|');

        $this->addDataForResource($resource, 'Channels', $channelSlug);
    }

    private function addPriceData(ProductVariantInterface $resource): void
    {
        /** @var ProductInterface $product */
        $product = $resource->getProduct();

        /** @var ChannelInterface[] $channels */
        $channels = $product->getChannels();
        foreach ($channels as $channel) {
            /** @var ChannelPricingInterface|null $channelPricing */
            $channelPricing = $resource->getChannelPricingForChannel($channel);

            if (null === $channelPricing) {
                continue;
            }

            $this->addDataForResource(
                $resource,
                'Price',
                $this->moneyFormatter->formatWithDecimals($channelPricing->getPrice())
            );
        }
    }

    private function addDeliveryData(ProductVariant $resource): void
    {
        $this->addDataForResource($resource, 'DeliveryCalculationMode', $resource->getDeliveryCalculationMode());
        $this->addDataForResource($resource, 'MinDayDelivery', $resource->getMinDayDelivery());
        $this->addDataForResource($resource, 'MaxDayDelivery', $resource->getMaxDayDelivery());
        $this->addDataForResource(
            $resource,
            'DeliveryCategory',
            null !== $resource->getShippingCategory() ? $resource->getShippingCategory()->getCode() : null
        );
    }
}
