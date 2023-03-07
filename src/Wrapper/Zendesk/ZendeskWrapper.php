<?php

declare(strict_types=1);

namespace App\Wrapper\Zendesk;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Zendesk\API\Exceptions\ApiResponseException;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\API\HttpClient as ZendeskAPI;

class ZendeskWrapper
{
    // Conversion array for system locales vs Zendesk locales
    private array $systemToZendeskLocalesConversion = [
      'en' => 'en-gb',
    ];

    private const CACHE_EXPIRATION_TIME = 3600 * 24; //24 hours

    private const ARTICLE_BASE_URL = 'api/v2/help_center{/locale}/articles/';
    private const ARTICLE_BASE_URL_LOCALE_PARAMETER = '{/locale}';
    private const CACHE_CATEGORIES_KEY = 'faq.categories.';
    private const CACHE_ARTICLE_KEY = 'faq.article.';

    /**
     * @see https://developer.zendesk.com/rest_api/docs/help_center/introduction
     */
    private ZendeskAPI $httpClient;
    private LoggerInterface $logger;
    private FilesystemAdapter $cache;

    public function __construct(
        string $zendeskApiSubdomain,
        string $zendeskApiUsername,
        string $zendeskApiToken,
        string $defaultCacheDir,
        LoggerInterface $logger
    ) {
        $this->cache = new FilesystemAdapter('zendesk', self::CACHE_EXPIRATION_TIME, $defaultCacheDir);
        $this->logger = $logger;
        $this->checkCredentials([
            'zendeskApiSubdomain' => $zendeskApiSubdomain,
            'zendeskApiUsername' => $zendeskApiUsername,
            'zendeskApiToken' => $zendeskApiToken,
        ]);

        $this->httpClient = new ZendeskAPI($zendeskApiSubdomain);
        try {
            $this->httpClient->setAuth(
                'basic',
                [
                    'username' => $zendeskApiUsername,
                    'token' => $zendeskApiToken
                ]
            );
        } catch (AuthException $e) {
            $this->logger->critical('An auth exception has occurred, Zendesk .env values may be not well configured');
        }
    }

    /**
     * return a list of categories,
     * every category contains a list of sections,
     * every section contains a list of articles
     * @throws \JsonException
     */
    public function getAllFAQ(string $systemLocale): array
    {
        $locale = $this->adaptSystemLocaleToZendeskLocale($systemLocale);
        $categoriesCacheKey = self::CACHE_CATEGORIES_KEY . $locale ?? self::CACHE_CATEGORIES_KEY;
        $categoriesCacheValue = $this->cache->getItem($categoriesCacheKey)->get();
        if (null !== $categoriesCacheValue) {
            return $categoriesCacheValue;
        }

        $categories = $this->toAssociative($this->getCategories($locale));
        $sections = $this->toAssociative($this->getSections($locale));
        $articles = $this->toAssociative($this->getArticles($locale));
        foreach ($sections as $section) {
            $section['articles'] = array_values(
                array_filter(
                    $articles,
                    static fn (array $article): bool => $article['section_id'] === $section['id'] && !$article['draft']
                    && null === $article['user_segment_id']
                )
            );
            $sections[] = $section;
        }

        foreach ($categories as $category) {
            $category['sections'] = array_values(
                array_filter(
                    $sections,
                    static fn (array $section): bool => $section['category_id'] === $category['id']
                )
            );
            $categories[] = $category;
        }

        return $this->cache->get($categoriesCacheKey, fn (): array => $categories);
    }

    /**
     * @throws \JsonException
     */
    public function getArticle(int $id, string $systemLocale): ?array
    {
        $locale = $this->adaptSystemLocaleToZendeskLocale($systemLocale);
        $articleCacheKey = self::CACHE_ARTICLE_KEY . $locale . '.' . $id;
        $articleCacheValue = $this->cache->getItem($articleCacheKey)->get();
        if (null !== $articleCacheValue) {
            return $articleCacheValue;
        }

        try {
            $article = $this->toAssociative((array)$this->httpClient->get($this->getArticleEndpoint($id, $locale)));
            return $this->cache->get($articleCacheKey, fn (): array => $article['article']);
        } catch (ApiResponseException | AuthException $e) {
            return null;
        }
    }

    private function getCategories(?string $locale = null): ?array
    {
        try {
            $categories = (array)$this->httpClient->helpCenter->categories()->setLocale($locale)->findAll();
            if ($categories['page_count'] > 1) {
                $categories = (array)$this->httpClient->helpCenter->categories()->setLocale($locale)->findAll(
                    [
                        'page_count' => 1,
                        'per_page' => $categories['count']
                    ]
                );
            }

            return $categories['categories'];
        } catch (ApiResponseException | AuthException $e) {
            return null;
        }
    }

    private function getSections(?string $locale = null): ?array
    {
        try {
            $sections = (array)$this->httpClient->helpCenter->sections()->setLocale($locale)->findAll();
            if ($sections['page_count'] > 1) {
                $sections = (array)$this->httpClient->helpCenter->sections()->setLocale($locale)->findAll(
                    [
                        'page_count' => 1,
                        'per_page' => $sections['count']
                    ]
                );
            }

            return $sections['sections'];
        } catch (ApiResponseException | AuthException $e) {
            return null;
        }
    }

    private function getArticles(?string $locale = null): ?array
    {
        try {
            $articles = (array)$this->httpClient->helpCenter->articles()->setLocale($locale)->findAll();
            if ($articles['page_count'] > 1) {
                $articles = (array)$this->httpClient->helpCenter->articles()->setLocale($locale)->findAll(
                    [
                        'page_count' => 1,
                        'per_page' => $articles['count']
                    ]
                );
            }

            return $articles['articles'];
        } catch (ApiResponseException | AuthException $e) {
            return null;
        }
    }

    /**
     * @param array|null $array
     * @return array
     * @throws \JsonException
     */
    private function toAssociative(?array $array): array
    {
        return $array !== null ? json_decode(json_encode($array, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR) : [];
    }

    /**
     * check if .env Zendesk variables are empty or not.
     * if not a log message will be printed.
     *
     * @param array $envVariables
     */
    private function checkCredentials(array $envVariables): void
    {
        foreach ($envVariables as $key => $envVariable) {
            if (empty($envVariable)) {
                $this->logger->critical($key . ' must be specified');
            }
        }
    }

    private function getArticleEndpoint(int $id, ?string $locale = null): string
    {
        return str_replace(self::ARTICLE_BASE_URL_LOCALE_PARAMETER, isset($locale) ? '/' . $locale : '', self::ARTICLE_BASE_URL) . $id;
    }

    private function adaptSystemLocaleToZendeskLocale(?string $locale): string
    {
        if (array_key_exists($locale, $this->systemToZendeskLocalesConversion)) {
            return $this->systemToZendeskLocalesConversion[$locale];
        }
        if (strpos($locale, '_') !== false) {
            $locale = str_replace('_', '-', $locale);
        }
        return strtolower($locale);
    }
}
