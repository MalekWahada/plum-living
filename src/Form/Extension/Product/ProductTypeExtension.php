<?php

declare(strict_types=1);

namespace App\Form\Extension\Product;

use App\Entity\Product\Product;
use App\Form\Type\Product\ProductGroupAutocompleteChoiceType;
use App\Form\Type\Resources\ProductCompleteInfo\ProductCompleteInfoType;
use App\Product\Image\DefaultMainImageAssigner;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductTypeExtension extends AbstractTypeExtension
{
    private DefaultMainImageAssigner $imageAssigner;

    public function __construct(DefaultMainImageAssigner $imageAssigner)
    {
        $this->imageAssigner = $imageAssigner;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'app.form.product.position',
            ])
            ->add('completeInfo', ProductCompleteInfoType::class, [
            'label' => false,
            ])
            ->add('groups', ProductGroupAutocompleteChoiceType::class, [
                'label' => 'app.form.product.groups',
                'multiple' => true
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Product $product */
            $product = $event->getData();
            $event->setData($this->imageAssigner->setMainTypeImage($product));
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
