<?php

declare(strict_types=1);

namespace App\Provider\CMS\Page;

use App\Entity\Page\Page;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use MonsieurBiz\SyliusCmsPagePlugin\Repository\PageRepository;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;

class PageProvider
{
    private PageRepository $pageRepository;
    private LocaleProviderInterface $localeProvider;

    public function __construct(
        PageRepository $pageRepository,
        LocaleProviderInterface $localeProvider
    ) {
        $this->pageRepository = $pageRepository;
        $this->localeProvider = $localeProvider;
    }

    /**
     * return the list of pages having a specific type.
     *
     * @param array $pageTypes
     * @return array
     */
    public function getPagesByType(array $pageTypes): array
    {
        $query = $this->pageRepository->createQueryBuilder('p');
        $query->addSelect('p')
            ->addSelect('t')
            ->addSelect('i')
            ->innerJoin('p.image', 'i')
            ->innerJoin('p.translations', 't', Join::WITH, 't.locale = :locale')
            ->andWhere(
                $query->expr()->in('p.type', ':pageType')
            )
            ->andWhere('p.enabled = true');
        $query->setParameters([
            'pageType' => $pageTypes,
            'locale' => $this->localeProvider->getDefaultLocaleCode(),
        ]);

        return $query->getQuery()->getResult();
    }

    /**
     * return the list of pages matching a provided list of codes for a locale
     *
     * @param array $pagesCodes
     * @param string $locale
     * @return array
     */
    public function getPagesContentsForLocale(array $pagesCodes, string $locale): array
    {
        $query = $this->pageRepository->createQueryBuilder('p');
        $query
            ->addSelect('t')
            ->addSelect('i')
            ->innerJoin('p.image', 'i')
            ->innerJoin('p.translations', 't')
            ->andWhere('t.locale = :locale')
            ->andWhere(
                $query->expr()->in('p.code', ':pagesCodes')
            )
            ->andWhere('p.enabled = true')
            ->setParameter('pagesCodes', $pagesCodes)
            ->setParameter('locale', $locale);

        return $query->getQuery()->getResult();
    }

    /**
     * get a single page matching given type and code.
     */
    public function getPageByTypeAndCode(?string $pageType = null, ?string $pageCode = null): ?Page
    {
        if (null === $pageCode && null === $pageType) {
            return null;
        }

        $criteria = [];
        if (null !== $pageCode) {
            $criteria['code'] = $pageCode;
        }
        if (null !== $pageType) {
            $criteria['type'] = $pageType;
        }

        return $this->pageRepository->findOneBy($criteria);
    }

    /**
     * retrieve QueryBuilder
     */
    public function getBaseQueryBuilder(
        string $locale,
        string $type,
        int $page = 0,
        int $limit = 50
    ): QueryBuilder {
        return $this->pageRepository
            ->createQueryBuilder('p')
            ->addSelect('t')
            ->addSelect('i')
            ->innerJoin('p.image', 'i')
            ->innerJoin('p.translations', 't')
            ->andWhere('p.enabled = true')
            ->andWhere('t.locale = :locale')
            ->andWhere('p.type = :type')
            ->setParameter('locale', $locale)
            ->setParameter('type', $type)
            ->addOrderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'DESC')
            ->setFirstResult($page * $limit)
            ->setMaxResults($limit);
    }

    /**
     * retrieve pages of the same category
     */
    public function getPagesEnabledByTypeAndCategoryForLocale(
        string $locale,
        string $type,
        ?string $category,
        int $page = 0,
        int $limit = 50
    ): Paginator {
        $query = $this->getBaseQueryBuilder($locale, $type, $page, $limit)
            ->andWhere('p.category = :category')
            ->setParameter('category', $category);

        return new Paginator($query, $fetchJoinCollection = true);
    }

    /**
     * retrieve pages of the same theme
     */
    public function getPagesEnabledByTypeAndThemeForLocale(
        string $locale,
        string $type,
        int $theme,
        int $page = 0,
        int $limit = 50
    ): Paginator {
        $query = $this->getBaseQueryBuilder($locale, $type, $page, $limit)
            ->innerJoin('p.themes', 'pt')
            ->andWhere('pt.theme = :theme')
            ->setParameter('theme', $theme);

        return new Paginator($query, $fetchJoinCollection = true);
    }

    /**
     * Seek for a CMS pages by type and content. Content string is an array of UI elements.
     * A single UI content search criteria is returned from getUIContentString function.
     *
     * @param string $pageType
     * @param string $locale
     * @param array $contents
     * @return array|null
     */
    public function getPagesByContent(
        string $pageType,
        string $locale,
        array $contents
    ): ?array {
        $qb = $this->pageRepository->createQueryBuilder('p');
        $qb->innerJoin('p.translations', 't', Join::WITH, 't.locale = :locale');
        $qb->where('p.type = :pageType');
        $qb->setParameters([
            'pageType' => $pageType,
            'locale' => $locale,
        ]);

        foreach ($contents as $key => $content) {
            $qb->andWhere('t.content LIKE :contentFilterString' . $key);
            $qb->setParameter(
                'contentFilterString' . $key,
                $content
            );
        }

        $qb->orderBy('p.position', Criteria::ASC);
        $qb->addOrderBy('p.updatedAt', Criteria::DESC);

        return $qb->getQuery()->getResult();
    }
}
