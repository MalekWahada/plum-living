<?php

declare(strict_types=1);

namespace App\Translation;

use App\Exception\Translation\MissingResourceTranslationKeyException;
use App\Exception\Translation\MissingResourceTranslationsForLocaleException;
use App\Exception\Translation\TranslatorClientLocaleNotFoundException;
use App\Model\Translation\TranslationBag;
use App\Model\Translation\TranslationKey;
use App\Translation\Client\TranslatorClientInterface;
use Psr\Log\LoggerInterface;

abstract class TranslationTask implements TranslationTaskInterface
{
    protected TranslatorClientInterface $translatorClient;
    protected LoggerInterface $logger;
    protected bool $lokalizeFechOnlyVerifiedKeys;

    public function __construct(TranslatorClientInterface $translatorClient, LoggerInterface $logger, bool $lokalizeFechOnlyVerifiedKeys)
    {
        $this->translatorClient = $translatorClient;
        $this->logger = $logger;
        $this->lokalizeFechOnlyVerifiedKeys = $lokalizeFechOnlyVerifiedKeys;
    }

    /**
     * @throws MissingResourceTranslationKeyException
     * @throws TranslatorClientLocaleNotFoundException
     * @throws MissingResourceTranslationsForLocaleException
     */
    protected function validateFetchedBag(TranslationBag $bag, TranslationBag $refBag, string $locale): void
    {
        // Check locale exist in translator client
        if (!$bag->hasLocale($locale)) {
            throw new TranslatorClientLocaleNotFoundException($locale);
        }

        // Check if the keys match with the reference locale bag
        $bagDiff = array_diff($refBag->getKeysNames(true), $bag->getKeysNames()); // Empty reference keys are removed as they should not be translated
        if (count($bagDiff) > 0) {
            throw new MissingResourceTranslationKeyException($bagDiff);
        }

        // Check if some keys are not translated in the requested language
        $missingTranslations = $bag->getEmptyKeysForLocale($locale);
        if (count($missingTranslations) > 0) {
            throw new MissingResourceTranslationsForLocaleException($locale, array_map(static function (TranslationKey $key) {
                return $key->getName();
            }, $missingTranslations));
        }
    }
}
