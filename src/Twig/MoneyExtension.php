<?php

declare(strict_types=1);

namespace App\Twig;

use App\Calculator\ProductPriceTaxCalculator;
use App\Entity\CustomerProject\Project;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Entity\Taxation\TaxRate;
use App\Formatter\Money\MoneyFormatter;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyExtension extends AbstractExtension
{
    private ProductPriceTaxCalculator $priceWithTaxCalculator;
    private MoneyFormatter $moneyFormatter;

    public function __construct(
        ProductPriceTaxCalculator $priceWithTaxCalculator,
        MoneyFormatter $moneyFormatter
    ) {
        $this->priceWithTaxCalculator = $priceWithTaxCalculator;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function getFilters(): iterable
    {
        return [
            new TwigFilter('calculate_price_with_tax', [$this, 'calculatePriceWithTax']),
            new TwigFilter('calculate_total_with_tax', [$this, 'calculateTotalWithTax']),
            new TwigFilter('calculate_total_items_with_tax', [$this, 'calculateTotalItemsWithTax']),
            new TwigFilter('format_price_without_decimals', [$this, 'formatPriceWithoutDecimals']),
            new TwigFilter('calculate_project_total', [$this, 'calculateProjectTotal']),
        ];
    }

    public function calculatePriceWithTax(ProductVariantInterface $variant, ?array $context = [], ?TaxRate $taxRate = null): int
    {
        return $this->priceWithTaxCalculator->calculate($variant, $context, $taxRate);
    }

    public function calculateTotalWithTax(OrderItem $orderItem, ?array $context = []): int
    {
        return $this->calculatePriceWithTax($orderItem->getVariant(), $context) * $orderItem->getQuantity();
    }

    public function formatPriceWithoutDecimals(int $amount, string $currencyCode, string $locale): string
    {
        return $this->moneyFormatter->formatWithoutDecimals($amount, $currencyCode, $locale);
    }

    public function calculateProjectTotal(Project $project, ?array $context = []): int
    {
        $projectTotalPrice = 0;

        foreach ($project->getItems() as $item) {
            $chosenVariant = $item->getChosenVariant();

            if (null === $chosenVariant) {
                continue;
            }

            $variant = $chosenVariant->getProductVariant();

            $projectTotalPrice+= $this->calculatePriceWithTax($variant, $context) * $chosenVariant->getQuantity();
        }

        return $projectTotalPrice;
    }

    public function calculateTotalItemsWithTax(Order $order, ?array $context = []): int
    {
        $totalItemsAmount = 0;

        foreach ($order->getItems() as $orderItem) {
            $totalItemsAmount += $orderItem->getTotal();
        }
        return $totalItemsAmount;
    }
}
