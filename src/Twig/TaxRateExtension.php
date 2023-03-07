<?php

namespace App\Twig;

use App\Entity\Channel\Channel;
use App\Entity\Taxation\TaxCategory;
use App\Entity\Taxation\TaxRate;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TaxRateExtension extends AbstractExtension
{
    private RepositoryInterface $taxRateRepository;
    private RepositoryInterface $taxCategoryRepository;
    private ChannelContextInterface $channelContext;

    public function __construct(
        ChannelContextInterface $channelContext,
        RepositoryInterface $taxRateRepository,
        RepositoryInterface $taxCategoryRepository
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->taxCategoryRepository = $taxCategoryRepository;
        $this->channelContext = $channelContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_default_tax_Rate', [$this, 'getDefaultTaxRate']),
        ];
    }

    public function getDefaultTaxRate(?TaxCategory $taxCategory = null) : ?TaxRate
    {
        $taxCategory = $taxCategory ?? $this->taxCategoryRepository->findOneBy(['code' => TaxCategory::DEFAULT_TAX_CATEGORY_CODE]);

        if (null === $taxCategory) {
            return null;
        }

        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();

        return $this->taxRateRepository->findOneBy([
            'category' => $taxCategory,
            'zone' =>  $channel->getDefaultTaxZone()
        ]);
    }
}
