<?php

declare(strict_types=1);

namespace App\Erp\Adapters;

use App\Erp\Adapters\Product\ProductAdapterInterface;
use App\Erp\Adapters\ProductVariant\ProductVariantAdapterInterface;
use Traversable;

/**
 * Provide all adapters for erp conversion
 * @link project://config/services/erp_adapters.yaml
 */
class AdapterProvider
{
    private array $productAdapters = [];
    private array $productVariantAdapters = [];

    // auto add adapters from tags defined in services.yml
    public function __construct(Traversable $adapters)
    {
        $adapters = iterator_to_array($adapters);

        foreach ($adapters as $adapter) {
            if ($adapter instanceof ProductVariantAdapterInterface) {
                $this->addProductVariantAdapter($adapter);
            } elseif ($adapter instanceof ProductAdapterInterface) {
                $this->addProductAdapter($adapter);
            }
        }
    }

    public function addProductAdapter(ProductAdapterInterface $adapter): void
    {
        $this->productAdapters[get_class($adapter)] = $adapter;
    }

    public function getProductAdapter(string $name): ?ProductAdapterInterface
    {
        return $this->productAdapters[$name] ?? null;
    }

    public function getProductAdapters(): array
    {
        return $this->productAdapters;
    }

    public function addProductVariantAdapter(ProductVariantAdapterInterface $adapter): void
    {
        $this->productVariantAdapters[get_class($adapter)] = $adapter;
    }

    public function getProductVariantAdapter(string $name): ?ProductVariantAdapterInterface
    {
        return $this->productVariantAdapters[$name] ?? null;
    }

    public function getProductVariantAdapters(): array
    {
        return $this->productVariantAdapters;
    }
}
