<?php

declare(strict_types=1);

namespace App\Twig;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class ProductAttributeValueExtension extends AbstractExtension
{
    private LocaleContextInterface $localeContext;
    private TranslatorInterface $translator;

    public function __construct(LocaleContextInterface $localeContext, TranslatorInterface $translator)
    {
        $this->localeContext = $localeContext;
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('product_attribute_value', [$this, 'getProductAttributeValue']),
        ];
    }

    public function getProductAttributeValue(ProductAttributeValueInterface $attributeValue, string $dateFormat = null): string
    {
        switch ($attributeValue->getType()) {
            case 'date':
                return date_format($attributeValue->getValue(), $dateFormat ?? 'd-m-Y');
            case 'datetime':
                return date_format($attributeValue->getValue(), $dateFormat ?? 'd-m-Y H:i:s');
            case 'select':
                $choices = null !== $attributeValue->getAttribute() ? $attributeValue->getAttribute()->getConfiguration()['choices'] : [];
                $values = [];
                foreach ($attributeValue->getValue() as $value) {
                    if (isset($choices[$value][$this->localeContext->getLocaleCode()])) {
                        $values[] = $choices[$value][$this->localeContext->getLocaleCode()];
                    }
                }
                return implode(', ', $values);
            case 'percent':
                return $attributeValue->getValue() * 100 . '%';
            case 'checkbox':
                return $attributeValue->getValue()
                    ? $this->translator->trans('sylius.ui.yes_label')
                    : $this->translator->trans('sylius.ui.no_label');
            default:
                return (string)$attributeValue->getValue();
        }
    }
}
