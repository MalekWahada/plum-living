<?php

declare(strict_types=1);

namespace App\Form\Extension\Customer;

use App\Entity\Customer\Customer;
use App\Provider\Customer\CustomerTypeChoicesProvider;
use App\Provider\Translation\SwitchableTranslationProvider;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerProfileType;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class CustomerProfileTypeExtension extends AbstractTypeExtension
{
    private CustomerTypeChoicesProvider $customerTypeChoicesProvider;
    private RequestStack $requestStack;
    private SwitchableTranslationProvider $translationProvider;
    private LocaleContextInterface $localeContext;

    public function __construct(
        CustomerTypeChoicesProvider $customerTypeChoicesProvider,
        RequestStack $requestStack,
        SwitchableTranslationProvider $translationProvider,
        LocaleContextInterface $localeContext
    ) {
        $this->customerTypeChoicesProvider = $customerTypeChoicesProvider;
        $this->requestStack = $requestStack;
        $this->translationProvider = $translationProvider;
        $this->localeContext = $localeContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('phoneNumber')
            ->add('phoneNumber', PhoneNumberType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.phone_number',
            ])
            ->remove('birthday')
            ->add('birthday', DateType::class, [
                'label' => 'app.form.customer.birthday',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd/MM/yyyy',
                'html5' => false,
            ])
            ->add('customerType', ChoiceType::class, [
                'label' => 'app.form.customer.customer_type.label',
                'choices' => $this->customerTypeChoicesProvider->getChoices(),
                'empty_data'  => null,
                'choice_label' => static fn (string $label): string => 'app.form.customer.customer_type.choices.' . $label,
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
            ->add('preferredTranslationSlug', ChoiceType::class, [ // TODO: in Symfony 5.2, use getter / setter instead of events
                'required' => true,
                'mapped' => false,
                'label' => 'app.form.customer.preferred_translation_slug',
                'choices' => $this->prepareTranslationSlugChoices($this->translationProvider->getTranslations(), $this->localeContext->getLocaleCode()),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (!$this->isShopAccountProfileRoute() || null === $event->getData()) { // Filter event as form can be called from CustomerAdminTypeExtension
                return;
            }

            /** @var Customer $data */
            $data = $event->getData();

            // Disable field if customer is B2B
            if ($data->hasB2BProgram()) {
                $event
                    ->getForm()
                    ->remove('customerType')
                    ->add('customerType', ChoiceType::class, [
                        'label' => 'app.form.customer.customer_type.label',
                        'choices' => $this->customerTypeChoicesProvider->getChoices(),
                        'empty_data'  => null,
                        'choice_label' => static fn (string $label): string => 'app.form.customer.customer_type.choices.' . $label,
                        'disabled' => true,
                    ])
                    ->remove('b2bProgram')
                    ->add('b2bProgram', CheckboxType::class, [
                        'required' => false,
                        'label' => 'sylius.form.customer.b2b_subscription',
                        'disabled' => true,
                    ])
                ;
            }
        });

        // Populate customer preferred translation slug
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Customer $data */
            $data = $event->getData();

            if (empty($data->getChannelCode()) || empty($data->getLocaleCode())) {
                return;
            }

            $slug = $this->translationProvider->findSlugFromChannelAndLocale($data->getChannelCode(), $data->getLocaleCode());
            $event->getForm()->get('preferredTranslationSlug')->setData($slug);
        });

        // Save customer preferred translation slug
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Customer $data */
            $data = $event->getData();

            $slug = $event->getForm()->get('preferredTranslationSlug')->getData();
            if (empty($slug)) {
                return;
            }

            if (null === $channel = $this->translationProvider->findChannelFromSlug($slug)) {
                return;
            }

            $localeCode = $this->translationProvider->findLocaleCodeFromSlug($slug, $channel->getCode());
            if (empty($localeCode)) {
                return;
            }

            $data->setChannelCode($channel->getCode());
            $data->setLocaleCode($localeCode);
        });
    }
    public static function getExtendedTypes(): iterable
    {
        return [CustomerProfileType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = [
                    'sylius',
                    'sylius_customer_profile',
                ];

                if ($form->getData()->hasB2bProgramOrIsEligibleToB2bProgram()) {
                    $validationGroups = array_merge($validationGroups, ['b2b_profile']);
                }

                return $validationGroups;
            },
        ]);
    }

    private function isShopAccountProfileRoute(): bool
    {
        return null !== $this->requestStack->getCurrentRequest() && $this->requestStack->getCurrentRequest()->get('_route') === 'sylius_shop_account_profile_update';
    }

    private function prepareTranslationSlugChoices(array $translations, string $localeCode): array
    {
        $choices = [];
        foreach ($translations as $translation) {
            $choices[$translation['names'][$localeCode]] = $translation['slug'];
        }
        return $choices;
    }
}
