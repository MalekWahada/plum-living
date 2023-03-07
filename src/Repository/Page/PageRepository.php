<?php

declare(strict_types=1);

namespace App\Repository\Page;

use MonsieurBiz\SyliusCmsPagePlugin\Repository\PageRepository as BasePageRepository;
use MonsieurBiz\SyliusCmsPagePlugin\Entity\PageInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

final class PageRepository extends BasePageRepository
{
    public function existsOneByChannelAndSlugAndType(ChannelInterface $channel, ?string $locale, string $slug, string $type): bool
    {
        $count = (int) $this
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->innerJoin('p.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere('translation.slug = :slug')
            ->andWhere(':channel MEMBER OF p.channels')
            ->andWhere('p.enabled = true')
            ->andWhere('p.type = :type')
            ->setParameter('channel', $channel)
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count > 0;
    }

    public function getOneByChannelAndSlugAndType(
        ChannelInterface $channel,
        ?string $locale,
        string $slug,
        string $type
    ): ?PageInterface {
        return $this
            ->createQueryBuilder('p')
            ->innerJoin('p.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere('translation.slug = :slug')
            ->andWhere(':channel MEMBER OF p.channels')
            ->andWhere('p.enabled = true')
            ->andWhere('p.type = :type')
            ->setParameter('channel', $channel)
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneForPreviewByPageId(int $pageId): ?PageInterface
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->andWhere('t.translatable = :pageId')
            ->setParameter('pageId', $pageId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findOneBySlugAndCategoryForLocale(
        string $locale,
        string $category,
        string $slug
    ): ?PageInterface {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->andWhere('p.enabled = true')
            ->andWhere('p.category = :category')
            ->andWhere('t.locale = :locale')
            ->andWhere('t.slug = :slug')
            ->setParameter('category', $category)
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findOneBySlugAndThemeForLocale(
        string $locale,
        int $theme,
        string $slug
    ): ?PageInterface {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->leftJoin('p.themes', 'pt')
            ->andWhere('p.enabled = true')
            ->andWhere('pt.theme = :theme')
            ->andWhere('t.locale = :locale')
            ->andWhere('t.slug = :slug')
            ->setParameter('theme', $theme)
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
