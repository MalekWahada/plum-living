<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use App\Entity\ProductIkea\ProductIkea;
use App\Entity\ProductIkea\ProductIkeaChannelPricing;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductIkeaChannelPricingType extends AbstractResourceType
{
    /** @var RepositoryInterface */
    private $channelPricingRepository;

    public function __construct(
        string $dataClass,
        array $validationGroups,
        ?RepositoryInterface $channelPricingRepository = null
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->channelPricingRepository = $channelPricingRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', MoneyType::class, [
                'label' => 'app.form.product_ikea.price',
                'currency' => $options['channel']->getBaseCurrency()->getCode(),
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options): void {
            $channelPricing = $event->getData();

            if (!$channelPricing instanceof $this->dataClass || !$channelPricing instanceof ProductIkeaChannelPricing) {
                $event->setData(null);

                return;
            }

            if ($channelPricing->getPrice() === null) {
                $event->setData(null);

                if ($channelPricing->getId() !== null) {
                    $this->channelPricingRepository->remove($channelPricing);
                }

                return;
            }

            $channelPricing->setChannelCode($options['channel']->getCode());
            $channelPricing->setProductIkea($options['product_ikea']);

            $event->setData($channelPricing);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('channel')
            ->setAllowedTypes('channel', [ChannelInterface::class])

            ->setDefined('product_ikea')
            ->setAllowedTypes('product_ikea', ['null', ProductIkea::class])

            ->setDefaults([
                'data_class' => ProductIkeaChannelPricing::class,
                'label' => function (Options $options): string {
                    return $options['channel']->getName();
                },
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_product_ikea_channel_pricing';
    }
}
