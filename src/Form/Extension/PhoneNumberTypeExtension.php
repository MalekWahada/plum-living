<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Addressing\Country;
use App\Entity\Channel\Channel;
use App\Form\DataTransformer\PhoneNumberToArrayWithFallbackTransformer;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use App\Validator\PhoneNumberConstraint\PhoneNumber as AssertPhoneNumber;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Provider\ChannelBasedLocaleProvider;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class PhoneNumberTypeExtension extends AbstractTypeExtension
{
    public const DEFAULT_REGION = 'FR';

    private PhoneNumberUtil $phoneNumberUtil;
    private RepositoryInterface $countryRepository;
    private ChannelContextInterface $channelContext;

    public function __construct(PhoneNumberUtil $phoneNumberUtil, RepositoryInterface $countryRepository, ChannelContextInterface $channelContext)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->countryRepository = $countryRepository;
        $this->channelContext = $channelContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();
        $channelDefaultCountryCode = $channel->getDefaultCountry() ? $channel->getDefaultCountry()->getCode() : self::DEFAULT_REGION;

        // Add transformer from string to PhoneNumber object
        $builder->addModelTransformer(new CallbackTransformer(
            function ($numberAsString) use ($channelDefaultCountryCode) {
                try {
                    return $this->phoneNumberUtil->parse($numberAsString, $channelDefaultCountryCode);
                } catch (NumberParseException $e) { // Unable to parse the number. Consider it as a FRENCH number to avoid data loss
                    $code = $this->phoneNumberUtil->getCountryCodeForRegion($channelDefaultCountryCode);
                    return (new PhoneNumber())->setRawInput($numberAsString)->setCountryCode($code);
                }
            },
            function ($numberAsPhoneNumber) {
                if ($numberAsPhoneNumber instanceof PhoneNumber) {
                    return $this->phoneNumberUtil->format($numberAsPhoneNumber, PhoneNumberFormat::E164);
                }
                return null;
            }
        ));

        $builder->resetViewTransformers(); // We must remove the original view transformer
        $builder->addViewTransformer(new PhoneNumberToArrayWithFallbackTransformer($options['country_choices'], $channelDefaultCountryCode));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Get available countries on the shop
        $enabledCountries = $this->countryRepository->findBy(["enabled" => true]);

        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();
        $channelDefaultCountryCode = $channel->getDefaultCountry() ? $channel->getDefaultCountry()->getCode() : self::DEFAULT_REGION;
        $channelCountriesCodes = $channel->getCountries()->map(function (CountryInterface $country) {
            return $country->getCode();
        })->toArray();

        $resolver->setDefaults([
            'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            'default_region' => $channelDefaultCountryCode,
            'format' => PhoneNumberFormat::NATIONAL,
            'country_choices' => array_map(static function (Country $country) {
                return $country->getCode();
            }, $enabledCountries),
            'preferred_country_choices' => $channelCountriesCodes,
            'constraints' => [
                new AssertPhoneNumber([
                    "regionPath" => "country",
                    "defaultRegion" => $channelDefaultCountryCode,
                    "groups" => [
                        "app_address",
                        "sylius"
                    ]
                ])
            ]
        ]);

        // Force only country choice widget
        $resolver->setAllowedValues('widget', [
            PhoneNumberType::WIDGET_COUNTRY_CHOICE
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            PhoneNumberType::class,
        ];
    }
}
