<?php

declare(strict_types=1);

namespace App\Provider\Translation;

use App\Entity\Channel\Channel;
use App\Entity\Locale\Locale;
use App\Exception\Translation\SwitchableTranslationsNotConfiguredException;
use Sylius\Bundle\ThemeBundle\Translation\Translator;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;

class SwitchableTranslationProvider
{
    private array $customCountryToTranslationSlugMapping = [
        'LU' => 'eu', // Use LU for Rest of Europe
    ];

    /**
     * @see Translator cannot be used as it's decorated by Sylius and is dependent on current Channel (causing looping)
     * Using translation key "app.header.shop.other_countries"
     */
    private array $customCountryToTranslationNameMapping = [
        'LU' => [
            'fr' => 'EU - Autres pays (anglais)',
            'en' => 'EU - Other countries (english)',
            'de' => 'EU - Andere Länder (Englisch)',
            'nl' => 'EU - Andere landen (Engels)',
        ],
    ];

    private array $customCountryToFlagMapping = [
        'LU' => 'eu', // Use LU for Rest of Europe
    ];

    private ChannelRepositoryInterface $channelRepository;
    private TranslationLocaleProvider $localeProvider;
    private ?array $cachedTranslations = null;

    public function __construct(ChannelRepositoryInterface $channelRepository, TranslationLocaleProvider $localeProvider)
    {
        $this->channelRepository = $channelRepository;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        if (null !== $this->cachedTranslations) {
            return $this->cachedTranslations;
        }

        /** @var Channel[] $channels */
        $channels = $this->channelRepository->findAll();

        $this->cachedTranslations = [];

        foreach ($channels as $channel) {
            if (null === $channel->getDefaultCountry() || null === $channel->getDefaultLocale()) {
                continue;
            }

            $countryCodes = $channel->getCountries()->map(fn (CountryInterface $country) => $country->getCode())->toArray();
            $defaultCountryCode = $channel->getDefaultCountry()->getCode();
            $otherCountryCodes = array_filter($countryCodes, static function (string $code) use ($defaultCountryCode) {
                return $code !== $defaultCountryCode;
            });
            $defaultLocaleCode = $channel->getDefaultLocale()->getCode();
            $countryFlag = array_key_exists($defaultCountryCode, $this->customCountryToFlagMapping) ? $this->customCountryToFlagMapping[$defaultCountryCode] : $defaultCountryCode;

            foreach ($channel->getLocales() as $locale) {
                $localeCode = $locale->getCode();
                $localeIsDefaultLocale = $defaultLocaleCode === $localeCode; // Always use SHORT code with the default locale.

                $slugCode = $this->generateSlugCode($defaultCountryCode, $localeCode, $localeIsDefaultLocale);
                $isLongCode = strlen($slugCode) > 2;

                /**
                 * Generate alternative slug for fallback.
                 */
                $alternativeSlugs = [];
                // Generate for custom country mapping: use original country code as is for alternatives (e.g lu)
                if (array_key_exists($defaultCountryCode, $this->customCountryToTranslationSlugMapping)) {
                    $alternativeSlugs[] = $this->generateSlugCode($defaultCountryCode, $localeCode, $localeIsDefaultLocale, false);
                    if ($localeIsDefaultLocale) { // Add LONG code as alternative if shortened above
                        $alternativeSlugs[] = $this->generateSlugCode($defaultCountryCode, $localeCode, false, false);
                    }
                }

                // LONG codes (4 letters, eg: nl-be) have no alternative.
                if (!$isLongCode) {
                    if ($localeIsDefaultLocale) { // Add LONG code as alternative if shortened above
                        $alternativeSlugs[] = $this->generateSlugCode($defaultCountryCode, $localeCode);
                    }

                    // Generate alternative codes for other countries.
                    foreach ($otherCountryCodes as $countryCode) {
                        $alternativeSlugs[] = $this->generateSlugCode($countryCode, $localeCode, $localeIsDefaultLocale);
                        if ($localeIsDefaultLocale) { // Add LONG code as alternative if shortened above
                            $alternativeSlugs[] = $this->generateSlugCode($countryCode, $localeCode);
                        }
                    }
                }

                $this->cachedTranslations[$slugCode] = [
                    'slug' => $slugCode,
                    'displayCode' => strtoupper(str_replace('-', '/', $slugCode)),
                    'alternativeSlugs' => $alternativeSlugs,
                    'localeCode' => $localeCode,
                    'countryCode' => $defaultCountryCode,
                    'countryFlag' => $countryFlag,
                    'channel' => $channel,
                    'names' => $this->generateNames($defaultCountryCode, $localeCode),
                    'isEnabled' => $channel->isEnabled(),
                ];
            }
        }

        if (empty($this->cachedTranslations)) {
            throw new SwitchableTranslationsNotConfiguredException();
        }

        return $this->cachedTranslations;
    }

    public function getDefaultSlug(): string
    {
        $translations = $this->getTranslations();
        $defaultLocaleCode = $this->localeProvider->getDefaultLocaleCode();

        if (isset($translations[$defaultLocaleCode])) {
            return $translations[$defaultLocaleCode]['slug'];
        }

        foreach ($translations as $translation) {
            if ($translation['localeCode'] === $defaultLocaleCode) {
                return $translation['slug'];
            }
        }

        return array_key_first($translations) ?? $defaultLocaleCode;
    }

    public function getTranslation(?string $slug): ?array
    {
        return $this->getTranslations()[$slug] ?? null;
    }

    public function getChannelTranslations(Channel $channel): array
    {
        return array_filter($this->getTranslations(), static function ($translation) use ($channel) {
            return $translation['channel'] === $channel;
        });
    }

    public function getChannelDefaultSlug(Channel $channel): ?string
    {
        $translations = $this->getTranslations();
        if (null === $channel->getDefaultCountry() || null === $channel->getDefaultLocale()) {
            return null;
        }

        $defaultCountryCode = $channel->getDefaultCountry()->getCode();
        $defaultLocaleCode = $channel->getDefaultLocale()->getCode();
        foreach ($translations as $translation) {
            if ($translation['countryCode'] === $defaultCountryCode && $translation['localeCode'] === $defaultLocaleCode) {
                return $translation['slug'];
            }
        }
        return null;
    }

    public function findChannelFromSlug(string $inputSlug): ?Channel
    {
        $translations = $this->getTranslations();
        if (isset($translations[$inputSlug])) {
            return $translations[$inputSlug]['channel'];
        }

        foreach ($translations as $slug => $translation) {
            if (in_array($inputSlug, $translation['alternativeSlugs'], true)) {
                return $translation['channel']; // Alternative slug is found. NonChannelSwitchableTranslationSlugListener will redirect to the correct slug.
            }
        }

        return null;
    }

    /**
     * @throws SwitchableTranslationsNotConfiguredException
     */
    public function findLocaleCodeFromSlug(string $inputSlug, ?string $channelCode): ?string
    {
        $translations = $this->getTranslations();

        if (null !== $channelCode) { // Filter by channel if provided
            $translations = array_filter($translations, static function (array $translation) use ($channelCode) {
                return $translation['channel']->getCode() === $channelCode;
            });
        }

        if (isset($translations[$inputSlug])) {
            return $translations[$inputSlug]['localeCode'];
        }

        return null;
    }

    public function findSlugFromChannelAndLocale(string $channelCode, string $localeCode): ?string
    {
        $translations = array_filter($this->getTranslations(), static function (array $translation) use ($channelCode) {
            return $translation['channel']->getCode() === $channelCode;
        });

        foreach ($translations as $slug => $translation) {
            if ($translation['localeCode'] === $localeCode) {
                return $slug;
            }
        }

        return null;
    }

    private function generateSlugCode(string $countryCode, ?string $localeCode, bool $forceShortCode = false, bool $useCustomMapping = true): string
    {
        if ($useCustomMapping && array_key_exists($countryCode, $this->customCountryToTranslationSlugMapping)) {
            return $this->customCountryToTranslationSlugMapping[$countryCode];
        }

        // SHORT code (2 letters)
        if (null === $localeCode || $forceShortCode) {
            return strtolower($countryCode);
        }

        // Existing locale LONG code (4 letters)
        if (strlen($localeCode) > 2) {
            return str_replace('_', '-', strtolower($localeCode));
        }

        // Custom LONG code (4 letters)
        return sprintf('%s-%s', strtolower($localeCode), strtolower($countryCode));
    }

    /**
     * @param string $countryCode
     * @param string $localeCode
     * @return array|string[]
     */
    private function generateNames(string $countryCode, string $localeCode): array
    {
        $names = [];
        foreach ($this->localeProvider->getDefinedLocalesCodesOrDefault() as $displayLocaleCode) {
            if (array_key_exists($countryCode, $this->customCountryToTranslationNameMapping)) { // Check for custom name mapping
                $names[$displayLocaleCode] = $this->customCountryToTranslationNameMapping[$countryCode][$displayLocaleCode] ?? $this->customCountryToTranslationNameMapping[$countryCode][Locale::DEFAULT_LOCALE_CODE];
                continue;
            }

            if (array_key_exists($countryCode, $this->customCountryToTranslationSlugMapping) || strtolower($countryCode) === strtolower($localeCode)) { // not a locale variant
                $names[$displayLocaleCode] = Countries::getName($countryCode, $displayLocaleCode);
                continue;
            }

            $names[$displayLocaleCode] = sprintf('%s (%s)', Countries::getName($countryCode, $displayLocaleCode), Languages::getName($localeCode, $displayLocaleCode));
        }
        return $names;
    }
}
