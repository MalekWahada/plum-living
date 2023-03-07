<?php

declare(strict_types=1);

namespace App\Repository\Channel;

use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository as BaseChannelRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class ChannelRepository extends BaseChannelRepository
{
    public function findOneByLocale(string $locale): ?ChannelInterface
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->addSelect('l')
            ->innerJoin('o.locales', 'l', 'WITH', 'l.code = :locale')
            ->andHaving('COUNT(l.id) = 1')
            ->groupBy('o.id')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByDefaultLocale(string $locale): ?ChannelInterface
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->addSelect('l')
            ->andWhere('d.code = :locale')
            ->innerJoin('o.defaultLocale', 'd')
            ->innerJoin('o.locales', 'l', 'WITH', 'l.code = :locale')
            ->andHaving('COUNT(l.id) = 1')
            ->groupBy('o.id')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByCountry(string $countryCode): ?ChannelInterface
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->addSelect('c')
            ->innerJoin('o.countries', 'c', 'WITH', 'c.code = :countryCode')
            ->andHaving('COUNT(c.id) = 1')
            ->groupBy('o.id')
            ->setParameter('countryCode', $countryCode);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
