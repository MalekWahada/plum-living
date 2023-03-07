<?php

declare(strict_types=1);

namespace App\Form\Type\Resources\Checkout;

use Sylius\Bundle\AddressingBundle\Form\Type\AddressType as SyliusAddressType;
use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerCheckoutGuestType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Addressing\Comparator\AddressComparatorInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerAwareInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Webmozart\Assert\Assert;

final class CheckoutAddressType extends AbstractResourceType
{
    private AddressComparatorInterface $addressComparator;

    public function __construct(
        AddressComparatorInterface $addressComparator,
        string $dataClass,
        array $validationGroups = []
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->addressComparator = $addressComparator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('differentBillingAddress', CheckboxType::class, [
            'mapped' => false,
            'required' => false,
            'label' => 'sylius.form.checkout.addressing.different_billing_address',
        ]);

        $builder->add('differentShippingAddress', CheckboxType::class, [
            'mapped' => false,
            'required' => false,
            'label' => 'sylius.form.checkout.addressing.different_shipping_address',
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $form = $event->getForm();

            Assert::isInstanceOf($event->getData(), OrderInterface::class);

            /** @var OrderInterface $order */
            $order = $event->getData();
            $channel = $order->getChannel();

            $form->add('shippingAddress', SyliusAddressType::class, [
                'shippable' => true,
                'constraints' => [new Valid()],
                'channel' => $channel,
            ]);

            $form->add('billingAddress', SyliusAddressType::class, [
                'constraints' => [new Valid()],
                'channel' => $channel,
            ]);

            // custom
            $form->get('shippingAddress')->add('shippingNotes', TextareaType::class, [
                'label' => 'app.form.address.shipping_notes.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'app.form.address.shipping_notes.placeholder',
                ],
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();

            Assert::isInstanceOf($event->getData(), OrderInterface::class);

            /** @var OrderInterface $order */
            $order = $event->getData();
            $areAddressesDifferent = $this->areAddressesDifferent($order->getBillingAddress(), $order->getShippingAddress());

            $form->get('differentBillingAddress')->setData($areAddressesDifferent);
            $form->get('differentShippingAddress')->setData($areAddressesDifferent);
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            $resource = $event->getData();
            $customer = $options['customer'];

            Assert::isInstanceOf($resource, CustomerAwareInterface::class);

            /** @var CustomerInterface|null $resourceCustomer */
            $resourceCustomer = $resource->getCustomer();

            if ((null === $customer && null === $resourceCustomer) ||
                (null !== $resourceCustomer && null === $resourceCustomer->getUser()) ||
                ($resourceCustomer !== $customer)
            ) {
                $form->add('customer', CustomerCheckoutGuestType::class, ['constraints' => [new Valid()]]);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $orderData = $event->getData();

            $differentBillingAddress = $orderData['differentBillingAddress'] ?? false;
            $differentShippingAddress = $orderData['differentShippingAddress'] ?? false;

            // Shipping-billing template forms are reversed, these 2 conditional should be reversed too
            // compared to the original implementation of '@SyliusCoreBundle/Form/Type/Checkout/AddressType.php'.
            if (isset($orderData['shippingAddress']) && !$differentBillingAddress && !$differentShippingAddress) {
                $orderData['billingAddress'] = $orderData['shippingAddress'];
            }

            if (isset($orderData['billingAddress']) && !$differentBillingAddress && !$differentShippingAddress) {
                $orderData['shippingAddress'] = $orderData['billingAddress'];
            }

            if (isset($orderData['billingAddress']['shippingNotes'])) {
                unset($orderData['billingAddress']['shippingNotes']);
            }

            $event->setData($orderData);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'customer' => null,
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_checkout_address';
    }

    private function areAddressesDifferent(?AddressInterface $firstAddress, ?AddressInterface $secondAddress): bool
    {
        if (null === $firstAddress || null === $secondAddress) {
            return false;
        }

        return !$this->addressComparator->equal($firstAddress, $secondAddress);
    }
}
