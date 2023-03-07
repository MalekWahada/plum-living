<?php

declare(strict_types=1);

namespace App\Translation\Client;

use App\Exception\Translation\TranslatorClientException;
use App\Model\Translation\TranslationBag;
use App\Model\Translation\TranslationBagInterface;
use App\Model\Translation\TranslationKey;
use Lokalise\Exceptions\LokaliseApiException;
use Lokalise\Exceptions\LokaliseResponseException;
use Lokalise\LokaliseApiClient;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class LokalizeClient implements TranslatorClientInterface
{
    private const PLATFORM_NAME = 'web';
    private const KEY_NAME_ALREADY_TAKEN_ERROR_CODE = 400;

    private array $projectIds;
    private LokaliseApiClient $client;
    private string $projectId;
    private LoggerInterface $logger;
    private RepositoryInterface $localeRepository;

    public function __construct(string $apiToken, array $projectIds, RepositoryInterface $localeRepository, LoggerInterface $translationLogger)
    {
        $this->projectIds = $projectIds;
        $this->localeRepository = $localeRepository;
        $this->client = new LokaliseApiClient($apiToken);
        $this->logger = $translationLogger;

        $this->setProject(self::CMS_PROJECT);
    }

    public function setProject(string $projectKey): void
    {
        if (!isset($this->projectIds[$projectKey])) {
            throw new \RuntimeException(sprintf('Lokalize project id "%s" is not provided', $projectKey));
        }

        $this->projectId = $this->projectIds[$projectKey];
    }

    /**
     * @throws TranslatorClientException
     */
    public function fetchAllKeys(TranslationBagInterface $bag, ?array $tagsFilter = null, ?array $keyNamesFilter = null, bool $translationsMustBeVerified = false): void
    {
        $availableLocaleCodes = array_map(static function ($locale) {
            return $locale->getCode();
        }, $this->localeRepository->findAll());

        try {
            $result = $this->client->keys->fetchAll($this->projectId, [
                'include_translations' => true,
                'filter_tags' => (null !== $tagsFilter) ? implode(',', $tagsFilter) : null, // Convert to key:value,key2:value2 format
                'filter_keys' => (null !== $keyNamesFilter) ? implode(',', $keyNamesFilter) : null, // Convert to key,key2 format
                'filter_archived' => 'exclude',
                'filter_platforms' => self::PLATFORM_NAME,
            ]);
            if (null === $result->body || !isset($result->body['keys'])) {
                throw new LokaliseResponseException('Lokalize response is not valid. No keys object found.');
            }

            foreach ($result->body['keys'] as $key) {
                if (!isset($key['tags'], $key['key_name'])) {
                    continue;
                }

                $keyName = $key['key_name'][self::PLATFORM_NAME];
                $tags = $this->explodeTagsFromLokalize($key['tags']);

                foreach ($key['translations'] as $translation) {
                    if (!isset($translation['key_id'], $translation['translation'], $translation['language_iso']) || !in_array($translation['language_iso'], $availableLocaleCodes, true)) {
                        continue;
                    }
                    if ($translationsMustBeVerified && $translation['is_unverified']) {
                        continue;
                    }
                    $translatableCode = $tags[TranslationKey::PRIMARY_TAG_KEY] ?? null;

                    // Add the key to the bag and reconstruct the tags
                    $bagKey = $bag->add($keyName, $translation['translation'], $translation['language_iso'], $translation['key_id'], $translatableCode);
                    foreach ($tags as $tagName => $tagValue) {
                        if (TranslationKey::PRIMARY_TAG_KEY === $tagName) {
                            continue;
                        }
                        $bagKey->addTag($tagName, $tagValue);
                    }
                }
            }
        } catch (LokaliseApiException $apiException) {
            $msg = sprintf('Lokalize API error: %s', $apiException->getMessage());
            $this->logger->error($msg);
            throw new TranslatorClientException($msg, $apiException->getCode(), $apiException);
        }
    }

    /**
     * @throws TranslatorClientException
     */
    public function publishKeys(TranslationBagInterface $bag): void
    {
        try {
            $filterTags = $bag->exportPrimaryTags();
            $retrieveBag = new TranslationBag();
            $this->fetchAllKeys($retrieveBag, $filterTags); // We must retrieve the translations to know if they already exist
            $this->matchLocalBagWithRetrieveBag($bag, $retrieveBag);

            $this->addOrUpdateKeys($bag, true, true); // Publish keys and retry for keys in error
        } catch (LokaliseApiException $apiException) {
            $msg = sprintf('Lokalize API error: %s', $apiException->getMessage());
            $this->logger->error($msg);
            throw new TranslatorClientException($msg, $apiException->getCode(), $apiException);
        }
    }

    /**
     * @param TranslationBagInterface $bag
     * @param bool $removeSkippedOrEmptyKeys
     * @param bool $retryKeysInError
     * @throws LokaliseApiException
     * @throws LokaliseResponseException
     * @throws TranslatorClientException
     */
    private function addOrUpdateKeys(TranslationBagInterface $bag, bool $removeSkippedOrEmptyKeys, bool $retryKeysInError): void
    {
        $keysToAddPayload = [];
        $keysToUpdatePayload = [];

        foreach ($bag->getAll() as $key) {
            $lokalizeKey = [
                'key_name' => $key->getName(),
                'translations' => [],
                'platforms' => [ self::PLATFORM_NAME ],
                'tags' => $bag->exportTags($key->getName()),
            ];

            foreach ($key->getTranslations($removeSkippedOrEmptyKeys) as $translation) {
                $lokalizeKey['translations'][] = [
                    'language_iso' => $translation->getLocale(),
                    'translation' => $translation->getValue() ?? '', // Translation must not be null
                ];
            }

            // Continue if no translations have changed, or it update the key and tags only (no settings translations will not delete them)
            if (empty($lokalizeKey['translations'])) {
                continue;
            }

            if (null === $keyId = $key->getId()) {
                $keysToAddPayload[] = $lokalizeKey;
            } else {
                $lokalizeKey['key_id'] = $keyId;
                $keysToUpdatePayload[] = $lokalizeKey;
            }
        }

        if (count($keysToAddPayload) > 0) {
            $add = $this->client->keys->create($this->projectId, [
                'keys' => array_reverse($keysToAddPayload), // Keys must be added in reverse in Lokalize
            ]);
            if ($retryKeysInError) {
                $this->processErrors($add->body, $bag);
            }
        }
        if (count($keysToUpdatePayload) > 0) {
            $update = $this->client->keys->bulkUpdate($this->projectId, [
                'keys' => array_reverse($keysToUpdatePayload),
            ]);
            if ($retryKeysInError) {
                $this->processErrors($update->body, $bag);
            }
        }
    }

    /**
     * @throws LokaliseApiException
     * @throws TranslatorClientException
     */
    private function processErrors(array $result, TranslationBagInterface $originalBag): void
    {
        $retryBag = new TranslationBag();

        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                // Search for already taken name error.
                // If found, we must reset the key tags as they may have been removed
                if (isset($error['code'], $error['key_name'][self::PLATFORM_NAME])
                    && $error['code'] === self::KEY_NAME_ALREADY_TAKEN_ERROR_CODE
                    && null !== $key = $originalBag->getKey($error['key_name'][self::PLATFORM_NAME])) {
                    $retryBag->addKey($key);
                    $this->logger->warning(sprintf('Key name "%s" is already taken in Lokalize. Trying to reassign tags to it.', $key->getName()));
                }
            }
        }

        if ($retryBag->isEmpty()) {
            return;
        }

        $retrieveBag = new TranslationBag();
        $this->fetchAllKeys($retrieveBag, null, $retryBag->getKeysNames()); // Get already existing keys
        $this->matchLocalBagWithRetrieveBag($retryBag, $retrieveBag);
        $this->addOrUpdateKeys($retryBag, false, false); // Force re-upload the keys with the new tags
    }

    /**
     * Explode key:value format tags from lokalize to an array $key => $value
     * @param array $tags
     * @return array
     */
    private function explodeTagsFromLokalize(array $tags): array
    {
        $result = [];
        foreach ($tags as $tag) {
            $explode = explode(':', $tag);
            if (count($explode) === 2) {
                $result[$explode[0]] = $explode[1];
            }
        }
        return $result;
    }

    /**
     * Implode a recursive $key => $value array to key:value format tags
     * @param array $tags
     * @return array
     */
    private function implodeTagsForLokalize(array $tags): array
    {
        $result = [];
        foreach ($tags as $key => $value) {
            if (is_array($value)) {
                foreach ($this->implodeTagsForLokalize($value) as $subTag) {
                    $result[] = $subTag;
                }
            }
            $result[] = sprintf('%s:%s', $key, $value);
        }
        return $result;
    }

    /**
     * Retrieve keys ids from Lokalize for current bag and check if they have changed
     * To update keys we must have their ids
     * @param TranslationBagInterface $bag
     * @param TranslationBag $retrieveBag
     */
    private function matchLocalBagWithRetrieveBag(TranslationBagInterface $bag, TranslationBag $retrieveBag): void
    {
        foreach ($bag->getAll() as $key) {
            if (null !== $retrievedKey = $retrieveBag->getKey($key->getName())) {
                $key->setId($retrievedKey->getId()); // Mandatory for update

                // Compare skipped keys
                foreach ($key->getTranslations() as $translation) {
                    $lokalizeTranslation = $retrievedKey->getTranslation($translation->getLocale()); // If key is not found in Lokalize, it will not be skipped
                    $translation->evaluateSkipped($lokalizeTranslation ? $lokalizeTranslation->getValue() : null);
                }
            }
        }
    }
}
