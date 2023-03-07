<?php

declare(strict_types=1);

namespace App\Form\Type\ProductIkea;

use App\Entity\ProductIkea\ProductIkeaTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductIkeaTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'app.form.product_ikea.name',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_ikea_translation';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductIkeaTranslation::class,
        ]);
    }
}
