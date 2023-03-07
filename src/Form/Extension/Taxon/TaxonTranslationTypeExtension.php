<?php

declare(strict_types=1);

namespace App\Form\Extension\Taxon;

use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonTranslation;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\RichEditorType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TaxonTranslationTypeExtension extends AbstractTypeExtension
{
    const ALLOWED_TAXONS = [
        Taxon::TAXON_FACADE_METOD_DOOR_CODE,
        Taxon::TAXON_FACADE_PAX_DOOR_CODE,
        Taxon::TAXON_FACADE_METOD_DRAWER_CODE,
        Taxon::TAXON_FACADE_METOD_PANEL_CODE,
        Taxon::TAXON_FACADE_PAX_PANEL_CODE
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            [$this, 'onPreSetData'],
        );
    }

    public function onPreSetData(FormEvent $formEvent): void
    {
        /** @var TaxonTranslation|null $taxonTranslation */
        $taxonTranslation = $formEvent->getData();
        /** @var Taxon|null $taxon */
        $taxon = $taxonTranslation !== null ? $taxonTranslation->getTranslatable() : null;

        if ($taxon !== null) {
            $taxonCode = $taxon->getCode();
            $isAccessory = $taxonCode === Taxon::TAXON_ACCESSORY_CODE;
            $cmsTaxons = [...self::ALLOWED_TAXONS, Taxon::TAXON_PAINT_CODE];

            if (in_array($taxonCode, $cmsTaxons, true) || $isAccessory) {
                $formEvent->getForm()->add('productInfo', RichEditorType::class, [
                    'required' => false,
                    'label' => 'app.form.taxon_translation.product_info',
                    'tags' => [$isAccessory ? 'product_info_accessory' : 'product_info'],
                    'attr' => [
                        'allowed_infos' => $isAccessory ? 2 : 1,
                    ],
                ]);
            }
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [TaxonTranslationType::class];
    }
}
