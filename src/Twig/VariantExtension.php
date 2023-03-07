<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Product\Product;
use App\Provider\Product\ProductVariantsTaxedPricesProvider;
use App\Repository\Product\ProductVariantRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class VariantExtension extends AbstractExtension
{
    private ProductVariantRepository $productVariantRepository;
    private ProductVariantsTaxedPricesProvider $productVariantsTaxedPricesProvider;

    public function __construct(
        ProductVariantRepository $productVariantRepository,
        ProductVariantsTaxedPricesProvider $productVariantsTaxedPricesProvider
    ) {
        $this->productVariantRepository = $productVariantRepository;
        $this->productVariantsTaxedPricesProvider = $productVariantsTaxedPricesProvider;
    }

    public function getFilters(): iterable
    {
        return [
            new TwigFilter('app_tunnel_resolve_variant', [$this, 'resolveVariant']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_product_variant_taxed_prices', [$this, 'getTaxedPrices']),
        ];
    }

    public function resolveVariant(
        Product $product,
        ?string $facadeTypeCode,
        ?string $designCode,
        ?string $finishCode,
        ?string $colorCode
    ) : ProductVariantInterface {
        $selectedVariant = null;
        if (null !== $facadeTypeCode && null !== $designCode && null !== $finishCode && null !== $colorCode) {
            $selectedVariant = $this->productVariantRepository->findVariantByOptionValues(
                $product->getId(),
                $facadeTypeCode,
                $designCode,
                $finishCode,
                $colorCode
            );
        }

        if (null === $selectedVariant) {
            /** @var ProductVariantInterface|false $selectedVariant */
            $selectedVariant = $product->getVariants()->first();
            if (false === $selectedVariant) {
                throw new BadRequestHttpException('No variant found');
            }
        }

        return $selectedVariant;
    }

    public function getTaxedPrices(ProductInterface $product, ChannelInterface $channel): array
    {
        return $this->productVariantsTaxedPricesProvider->provideVariantsTaxedPrices($product, $channel);
    }
}
