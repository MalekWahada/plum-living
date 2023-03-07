<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\ProductInfo;

use App\Entity\Taxonomy\Taxon;
use App\Repository\Taxon\TaxonRepository;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductInfoAccessoryType extends AbstractType
{
    /** @var array|Taxon[] */
    private array $taxons;
    private LocaleContextInterface $localeContext;

    public function __construct(TaxonRepository $taxonRepository, LocaleContextInterface $localeContext)
    {
        $this->taxons = $taxonRepository->findChoicesFacadeTypes();
        $this->localeContext = $localeContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $taxonCodes = array_map(fn (Taxon $taxon): string => $taxon->getCode(), $this->taxons);

        $builder->add('facade_type', ChoiceType::class, [
            'choices' => $taxonCodes,
            'required' => true,
            'label' => 'app.form.product_info_accessory.facade_type',
            'choice_label' => fn (string $code): ?string => $this->getTaxonValue($code),
        ]);
        $builder->add('infos', ProductInfoType::class);
    }

    private function getTaxonValue(string $code): ?string
    {
        /**
         * @var Taxon $taxon
         */
        foreach ($this->taxons as $taxon) {
            if ($taxon->getCode() === $code) {
                return $taxon->getTranslation($this->localeContext->getLocaleCode())->getName();
            }
        }
        return null;
    }
}
