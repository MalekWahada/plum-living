<?php

declare(strict_types=1);

namespace App\Form\Extension\Customer;

use App\Provider\Customer\HowYouKnowAboutUsChoicesProvider;
use App\Provider\Customer\CustomerTypeChoicesProvider;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerRegistrationType;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerRegistrationTypeExtension extends AbstractTypeExtension
{
    private HowYouKnowAboutUsChoicesProvider $howYouKnowChoicesProvider;
    private CustomerTypeChoicesProvider $customerTypeChoicesProvider;

    public function __construct(
        HowYouKnowAboutUsChoicesProvider $howYouKnowChoicesProvider,
        CustomerTypeChoicesProvider $customerTypeChoicesProvider
    ) {
        $this->howYouKnowChoicesProvider = $howYouKnowChoicesProvider;
        $this->customerTypeChoicesProvider = $customerTypeChoicesProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('birthday', DateType::class, [
                'label' => 'app.form.customer.birthday',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'html5' => false,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'app.form.customer.gender.label',
                'required' => true,
                'choices' => [
                    'app.form.customer.gender.choices.women' => CustomerInterface::FEMALE_GENDER,
                    'app.form.customer.gender.choices.men' => CustomerInterface::MALE_GENDER,
                    'app.form.customer.gender.choices.not_indicated' => CustomerInterface::UNKNOWN_GENDER,
                ],
                'empty_data' => CustomerInterface::UNKNOWN_GENDER,
            ])
            ->add('howYouKnowAboutUs', ChoiceType::class, [
                'label' => 'app.form.customer.how_you_know_about_us.label',
                'choices' => $this->howYouKnowChoicesProvider->getChoices(),
                'choice_label' =>
                    fn (string $label): string => 'app.form.customer.how_you_know_about_us.choices.' . $label,
            ])
            ->add('customerType', ChoiceType::class, [
                'label' => 'app.form.customer.customer_type.label',
                'choices' => $this->customerTypeChoicesProvider->getChoices(),
                'empty_data'  => null,
                'choice_label' =>
                    fn (string $label): string => 'app.form.customer.customer_type.choices.' . $label,
            ])
            ->add('howYouKnowAboutUsDetails', TextType::class, [
                'required' => false,
            ])
            ->add('acceptPrivacyPolicy', CheckboxType::class, [
                'label' => 'app.form.customer.accept_privacy_policy',
                'mapped' => false,
            ])
            ->remove('phoneNumber')
            ->add('phoneNumber', PhoneNumberType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.phone_number',
            ])
            ->add('companyName', TextType::class, [
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
            ->add('b2bProgram', CheckboxType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.b2b_subscription',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (PostSubmitEvent $event) {
            if (!\in_array(
                $event->getData()->getCustomerType(),
                CustomerTypeChoicesProvider::CHOICES_B2B,
                true
            )) {
                $event->getData()->setB2bProgram(false); // As B2bProgram checkbox is true by default we need to reset it for personal customers
            }
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerRegistrationType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = [
                    'sylius',
                    'sylius_user_registration',
                    'sylius_customer_profile',
                ];

                if (\in_array(
                    $form->getData()->getCustomerType(),
                    CustomerTypeChoicesProvider::CHOICES_B2B,
                    true
                )) {
                    $validationGroups = array_merge($validationGroups, ['b2b_profile']);
                }

                return $validationGroups;
            },
        ]);
    }
}
