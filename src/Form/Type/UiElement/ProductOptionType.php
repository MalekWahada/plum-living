<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Entity\Product\ProductOption;
use App\Provider\CMS\ProductOptionCode\ProductOptionCodeProvider;
use App\Repository\Product\ProductOptionRepository;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductOptionType extends AbstractType
{
    private ProductOptionRepository $productOptionRepository;
    private ProductOptionCodeProvider $codeProvider;
    private LocaleContextInterface $localeContext;

    public function __construct(
        ProductOptionRepository $productOptionRepository,
        ProductOptionCodeProvider $codeProvider,
        LocaleContextInterface $localeContext
    ) {
        $this->productOptionRepository = $productOptionRepository;
        $this->codeProvider = $codeProvider;
        $this->localeContext = $localeContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ProductOption $optionsList */
        $optionsList = $this->productOptionRepository->findByCode($this->codeProvider->getProductOptionCodes());
        $optionsCodes = array_map(
            static fn (ProductOption $option): string => $option->getCode(),
            (array)$optionsList
        );

        $builder->add('option', ChoiceType::class, [
            'choices' => $optionsCodes,
            'label' => 'app.form.product_option.label',
            'multiple' => false,
            'choice_label' => fn (string $optionCode): string => current(array_filter(
                (array)$optionsList,
                static fn (ProductOption $option): bool => $option->getCode() === $optionCode
            ))->getTranslation($this->localeContext->getLocaleCode())->getName()
        ]);
    }
}
