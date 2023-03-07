<?php

declare(strict_types=1);

namespace App\Export\Plugin;

use App\Entity\Product\Product;
use App\Entity\Product\ProductTranslation;
use App\Formatter\Money\MoneyFormatter;
use App\Provider\ImportExport\LocalizedFieldsProvider;
use App\Transformer\MonsieurBiz\TextTransformer;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function rtrim;

final class ProductResourcePlugin extends TranslatableResourcePlugin
{
    private RepositoryInterface $channelPricingRepository;
    private MoneyFormatter $moneyFormatter;
    private TextTransformer $textTransformer;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        RepositoryInterface $channelPricingRepository,
        MoneyFormatter $moneyFormatter,
        TextTransformer $textTransformer,
        LocalizedFieldsProvider $provider
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager, $provider);
        $this->channelPricingRepository = $channelPricingRepository;
        $this->moneyFormatter = $moneyFormatter;
        $this->textTransformer = $textTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport); // Set Id, Code + Name, Description, ShortDescription, MetaDescription, MetaKeywords

        /** @var Product $resource */
        foreach ($this->resources as $resource) {
            $this->addTranslationData($resource);
            $this->addTaxonData($resource);
            $this->addAttributeData($resource);
            $this->addChannelData($resource);
            $this->addImageData($resource);
            $this->addPriceData($resource);
            $this->addDeliveryCategoryData($resource);
            $this->addGroups($resource);
        }
    }

    private function addTranslationData(ProductInterface $resource): void
    {
        $this->addDataForTranslatableResource(
            $resource,
            'DeliveryDescription',
            null,
            function ($value) {
                return $this->textTransformer->transform($value);
            }
        );
    }

    private function addTaxonData(ProductInterface $resource): void
    {
        $mainTaxonSlug = '';

        /** @var TaxonInterface|null $mainTaxon */
        $mainTaxon = $resource->getMainTaxon();
        if (null !== $mainTaxon) {
            $mainTaxonSlug = $mainTaxon->getCode();
        }

        $this->addDataForResource($resource, 'MainTaxon', $mainTaxonSlug);

        $taxonsSlug = '';
        $taxons = $resource->getTaxons();
        foreach ($taxons as $taxon) {
            $taxonsSlug .= $taxon->getCode() . '|';
        }

        $taxonsSlug = rtrim($taxonsSlug, '|');
        $this->addDataForResource($resource, 'Taxons', $taxonsSlug);
    }

    private function addChannelData(ProductInterface $resource): void
    {
        $channelSlug = '';

        /** @var ChannelInterface[] $channels */
        $channels = $resource->getChannels();
        foreach ($channels as $channel) {
            $channelSlug .= $channel->getCode() . '|';
        }

        $channelSlug = rtrim($channelSlug, '|');

        $this->addDataForResource($resource, 'Channels', $channelSlug);
    }

    private function addAttributeData(ProductInterface $resource): void
    {
        $attributes = $resource->getAttributes();

        /** @var AttributeValueInterface $attribute */
        foreach ($attributes as $attribute) {
            $this->addDataForResource($resource, $attribute->getCode(), $attribute->getValue());
        }
    }

    private function addImageData(ProductInterface $resource): void
    {
        $images = $resource->getImages();

        /** @var ImageInterface $image */
        foreach ($images as $image) {
            $this->addDataForResource(
                $resource,
                ImageTypesProvider::IMAGES_PREFIX . $image->getType(),
                $image->getPath()
            );
        }
    }

    private function addPriceData(ProductInterface $resource): void
    {
        if (!$resource->hasVariants()) {
            return;
        }

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $resource->getVariants()->first();

        /** @var ChannelInterface[] $channels */
        $channels = $resource->getChannels();
        foreach ($channels as $channel) {
            /** @var ChannelPricingInterface|null $channelPricing */
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channel->getCode(),
                'productVariant' => $productVariant,
            ]);

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

    private function addDeliveryCategoryData(ProductInterface $resource): void
    {
        if (!$resource->hasVariants()) {
            return;
        }

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $resource->getVariants()->first();

        $this->addDataForResource(
            $resource,
            'DeliveryCategory',
            null !== $productVariant->getShippingCategory() ? $productVariant->getShippingCategory()->getCode() : null
        );
    }

    private function addGroups(Product $resource): void
    {
        $groupsSlug = '';

        foreach ($resource->getGroups() as $group) {
            $groupsSlug .= $group->getCode() . '|';
        }

        $groupsSlug = rtrim($groupsSlug, '|');
        $this->addDataForResource($resource, 'Groups', $groupsSlug);
    }
}
