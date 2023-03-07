<?php

declare(strict_types=1);

namespace App\Form\Type\CustomerProject;

use App\Entity\CustomerProject\ProjectItem;
use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductOptionValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectItemVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('quantity', IntegerType::class, [
            'label' => false,
            'attr' => [
                'class' => 'quantity',
                'min' => 1,
            ],
            'empty_data' => 1
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectItemVariant::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_project_item_variant';
    }
}
