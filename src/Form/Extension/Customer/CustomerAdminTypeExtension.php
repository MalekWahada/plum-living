<?php

declare(strict_types=1);

namespace App\Form\Extension\Customer;

use App\Provider\Customer\CustomerTypeChoicesProvider;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerAdminTypeExtension extends AbstractTypeExtension
{
    private CustomerTypeChoicesProvider $customerTypeChoicesProvider;

    public function __construct(
        CustomerTypeChoicesProvider $customerTypeChoicesProvider
    ) {
        $this->customerTypeChoicesProvider = $customerTypeChoicesProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('phoneNumber')
            ->add('phoneNumber', PhoneNumberType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.phone_number',
            ])
            ->add('customerType', ChoiceType::class, [
                'label' => 'app.form.customer.customer_type.label',
                'choices' => $this->customerTypeChoicesProvider->getChoices(),
                'empty_data'  => null,
                'choice_label' =>
                    fn (string $label): string => 'app.form.customer.customer_type.choices.' . $label,
            ])
            ->add('b2bProgram', CheckboxType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.b2b_enabled',
            ])->add('companyName', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_name',
            ])
            ->add('companyStreet', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_address',
            ])
            ->add('companyPostcode', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_postcode',
            ])
            ->add('companyCity', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_city',
            ])
            ->add('companyCountryCode', CountryCodeChoiceType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_country',
            ])
            ->add('companyInstagram', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_instagram',
            ])
            ->add('companyWebsite', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.company_website',
            ])
            ->add('vatNumber', TextType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.vat_number',
            ])
            ->add('b2bCouponCode', TextType::class, [
                'required' => false,
                'disabled' => true,
                'mapped' => false,
                'label' => 'sylius.form.customer.b2b_coupon_code',
                'data' => $builder->getData()->getPersonalCoupon() ? $builder->getData()->getPersonalCoupon()->getCode() : '-',
            ])
        ;
    }

    /**
     * CAUTION: CustomerType inherits from CustomerProfileType
     * So @see CustomerProfileTypeExtension will also be called
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [CustomerType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = [
                    'sylius',
                    'sylius_customer_profile',
                    'sylius_admin_customer_profile',
                ];

                if ($form->getData()->hasB2bProgramOrIsEligibleToB2bProgram()) {
                    $validationGroups = array_merge($validationGroups, ['b2b_profile']);
                }

                return $validationGroups;
            },
        ]);
    }
}
