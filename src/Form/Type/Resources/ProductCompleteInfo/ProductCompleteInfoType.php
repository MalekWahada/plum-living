<?php

declare(strict_types=1);

namespace App\Form\Type\Resources\ProductCompleteInfo;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductCompleteInfoType extends AbstractResourceType
{
    public function __construct(string $dataClass, array $validationGroups = [])
    {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('enabled', CheckboxType::class, [
            'label' => 'sylius.ui.enabled',
            'required' => false,
        ]);
        $builder->add('translations', ResourceTranslationsType::class, [
            'entry_type' => ProductCompleteInfoTranslationType::class,
            'label' => 'sylius.ui.translations',
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_complete_info';
    }
}
