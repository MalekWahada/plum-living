<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer;

use ApiPlatform\Core\Serializer\ItemNormalizer;
use App\Entity\Order\Adjustment;
use App\Formatter\Money\MoneyFormatter;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Webmozart\Assert\Assert;

final class AdjustmentSerializer implements ContextAwareNormalizerInterface
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
        return $data instanceof Adjustment;
    }

    /**
     * @param mixed $object
     * @param string $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        Assert::isInstanceOf($object, Adjustment::class);

        $data = $this->normalizer->normalize($object, $format, $context);
        \assert(is_array($data));

        if (isset($data['amount'])) {
            $data['amount'] = $this->moneyFormatter->formatWithDecimals($data['amount']);
        }

        return $data;
    }
}
