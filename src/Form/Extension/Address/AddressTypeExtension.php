<?php

declare(strict_types=1);

namespace App\Form\Extension\Address;

use App\Entity\Addressing\Address;
use App\Entity\Addressing\Country;
use App\Provider\Address\ProvinceProvider;
use App\Provider\Address\ZIPCodeProvider;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressTypeExtension extends AbstractTypeExtension
{
    private ProvinceProvider $provinceProvider;
    private ZIPCodeProvider $ZIPCodeProvider;

    public function __construct(
        ProvinceProvider $provinceProvider,
        ZIPCodeProvider $ZIPCodeProvider
    ) {
        $this->provinceProvider = $provinceProvider;
        $this->ZIPCodeProvider = $ZIPCodeProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('phoneNumber', PhoneNumberType::class, [
            'required' => true,
            'label' => 'sylius.form.address.phone_number'
        ]);

        $builder->add('countryCode', CountryCodeChoiceType::class, [
            'label' => 'sylius.form.address.country',
            'enabled' => true,
            'choice_attr' => fn (?Country $country): array => $country === null ? [] : [
                'pattern' => $this->ZIPCodeProvider->getPattern($country->getCode()),
            ],
        ]);

        // Instead of asking the customer to chose his province, we can deduce it from its postCode
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var Address|null $address */
            $address = $event->getData();

            if (null === $address || null === $address->getPostcode()) {
                return;
            }

            $province = $this->provinceProvider->getProvinceFromPostCode($address->getPostcode(), $address->getCountryCode());
            $address->setProvinceCode($province->getCode());

            $event->setData($address);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => [
                'sylius',
                'app_address',
            ],
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [AddressType::class];
    }
}
