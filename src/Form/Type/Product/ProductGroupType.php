<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Entity\Product\ProductGroup;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAttributeValueType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAutocompleteChoiceType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductChoiceType;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductGroupTranslationType::class,
                'label' => 'app.form.product_group.translations',
            ])
            ->add('mainTaxon', TaxonAutocompleteChoiceType::class, [
                'label' => 'sylius.ui.main_taxon',
                'required' => true,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'app.form.product_group.position',
                'required' => false,
            ])
            ->add('products', ProductAutocompleteChoiceType::class, [
                'label' => 'app.form.product_group.products',
                'multiple' => true,
            ])
            ->addEventSubscriber(new AddCodeFormSubscriber())
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_group';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductGroup::class,
        ]);
    }
}
