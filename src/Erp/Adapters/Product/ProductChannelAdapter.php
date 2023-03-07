<?php

declare(strict_types=1);

namespace App\Erp\Adapters\Product;

use App\Entity\Product\Product;
use App\Model\Erp\ErpItemModel;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

class ProductChannelAdapter implements ProductAdapterInterface
{
    private ChannelRepositoryInterface $channelRepository;

    // defined with high priority
    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * Channels list are COUPLED to the ERP
     * A product is available on all channels
     * @param Product $product
     * @param ErpItemModel $erpItem
     */
    public function adaptProduct(Product $product, ErpItemModel $erpItem): void
    {
        foreach ($this->channelRepository->findAll() as $channel) {
            $product->addChannel($channel);
        }
    }
}
