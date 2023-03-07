<?php

declare(strict_types=1);

namespace App\Form\Type\ProductIkea;

use App\Entity\ProductIkea\ProductIkea;
use App\Form\Type\Resources\ProductIkeaChannelPricingType;
use App\Form\Type\Resources\ProductIkeaImageType;
use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductIkeaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'app.form.product_ikea.code',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductIkeaTranslationType::class,
                'label' => 'app.form.product_ikea.translations',
            ])
            ->add('image', ProductIkeaImageType::class, [
                'label' => 'sylius.form.image.file',
                'required' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $productIkea = $event->getData();

            $event->getForm()->add('channelPricings', ChannelCollectionType::class, [
                'entry_type' => ProductIkeaChannelPricingType::class,
                'entry_options' => function (ChannelInterface $channel) use ($productIkea) {
                    return [
                        'channel' => $channel,
                        'product_ikea' => $productIkea,
                    ];
                },
                'label' => 'sylius.form.variant.price',
            ]);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_ikea';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductIkea::class,
        ]);
    }
}
