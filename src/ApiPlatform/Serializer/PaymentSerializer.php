<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer;

use ApiPlatform\Core\Serializer\ItemNormalizer;
use App\Entity\Payment\Payment;
use App\Formatter\Money\MoneyFormatter;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Webmozart\Assert\Assert;

final class PaymentSerializer implements ContextAwareNormalizerInterface
{
    private ItemNormalizer $normalizer;
    private MoneyFormatter $moneyFormatter;

    public function __construct(
        ItemNormalizer $normalizer,
        MoneyFormatter $moneyFormatter
    ) {
        $this->normalizer = $normalizer;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Payment;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, Payment::class);

        $data = $this->normalizer->normalize($object, $format, $context);
        \assert(is_array($data));

        // Format each items in data recursively starting with "amount" in key
        // Beware that the object "details" in Payment is generated by the payment provider and its implementation could change depending on our provider choice
        array_walk_recursive($data, function (&$item, $key) {
            if (is_string($key) && str_starts_with($key, "amount") && is_int($item)) {
                $item = $this->moneyFormatter->formatWithDecimals($item);
            }
        });

        return $data;
    }
}