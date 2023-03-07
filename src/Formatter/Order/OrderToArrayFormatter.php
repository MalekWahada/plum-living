<?php

declare(strict_types=1);

namespace App\Formatter\Order;

use App\Calculator\ProductPriceTaxCalculator;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Entity\Product\Product;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Core\Model\Image;
use Symfony\Component\Asset\Packages;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class OrderToArrayFormatter
{
    private CacheManager $imagineCacheManager;
    private MoneyFormatterInterface $moneyFormatter;
    private ProductPriceTaxCalculator $priceWithTaxCalculator;
    private Packages $package;
    private OrderRepository $orderRepository;

    public function __construct(
        CacheManager $imagineCacheManager,
        MoneyFormatterInterface $moneyFormatter,
        ProductPriceTaxCalculator $priceWithTaxCalculator,
        Packages $package,
        OrderRepository $orderRepository
    ) {
        $this->imagineCacheManager = $imagineCacheManager;
        $this->moneyFormatter = $moneyFormatter;
        $this->priceWithTaxCalculator = $priceWithTaxCalculator;
        $this->package = $package;
        $this->orderRepository = $orderRepository;
    }

    public function format(Order $order) :array
    {
        $orderArray = [];
        $totalOrder = 0;
        $orderArray['itemCount'] = $order->getTotalQuantity();
        $currencyCode = $order->getCurrencyCode();
        $localeCode = $order->getLocaleCode();
        if (!$order->getItems()->isEmpty()) {
            foreach ($order->getItems() as $item) {
                $itemArray = [];
                $itemArray['id'] = $item->getId();
                $itemArray['name'] = $item->getProductName();
                $itemArray['quantity'] = $item->getQuantity();
                $itemArray['options'] = $this->getValuesAsArray($item);
                $orderItemTotal = $this->priceWithTaxCalculator->calculate($item->getVariant()) * $item->getQuantity();
                $totalOrder += $orderItemTotal;
                $itemArray['totalPrice'] = $this->moneyFormatter->format(
                    $orderItemTotal,
                    $currencyCode,
                    $localeCode
                );

                /** @var Product $product */
                $product = $item->getVariant()->getProduct();
                if (!$product->getImagesByType('thumbnail')->isEmpty()) {
                    $image = $product->getImagesByType('thumbnail')->first();
                    \assert($image instanceof Image);
                    $imagePath = $image->getPath();
                } elseif (!$product->getImages()->isEmpty()) {
                    $image = $product->getImages()->first();
                    \assert($image instanceof Image);
                    $imagePath = $image->getPath();
                } else {
                    $imagePath= $this->package->getUrl('build/shop/images/no-image.png', 'shop');
                }

                $itemArray['image'] = $this->imagineCacheManager->getBrowserPath($imagePath, 'sylius_shop_product_thumbnail');
                $orderArray['items'][] = $itemArray;
            }
        } else {
            $this->orderRepository->remove($order);
        }

        $orderArray['total'] = $this->moneyFormatter->format(
            $totalOrder,
            $currencyCode,
            $localeCode
        );

        return $orderArray;
    }

    private function getValuesAsArray(OrderItem $item) :array
    {
        $optionValues = $item->getVariant()->getOptionValues();
        $values = [];

        /** @var ProductOptionValue $optionValue */
        foreach ($optionValues as $optionValue) {
            $values[] = $optionValue->getValue();
        }

        return $values;
    }
}
