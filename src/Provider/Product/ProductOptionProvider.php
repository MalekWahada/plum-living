<?php

declare(strict_types=1);

namespace App\Provider\Product;

use App\Entity\Product\ProductOption;
use App\Factory\Product\ProductOptionFactory;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;
use Sylius\Component\Resource\Factory\TranslatableFactory;

class ProductOptionProvider
{
    /**
     * @var ProductOptionRepositoryInterface
     */
    private ProductOptionRepositoryInterface $productOptionRepository;

    /**
     * @var TranslatableFactory
     */
    private TranslatableFactory $productOptionFactory;


    /**
     * ProductOptionProvider constructor.
     * @param ProductOptionRepositoryInterface $productOptionRepository
     * @param TranslatableFactory $productOptionFactory
     */
    public function __construct(
        ProductOptionRepositoryInterface $productOptionRepository,
        TranslatableFactory $productOptionFactory
    ) {
        $this->productOptionRepository = $productOptionRepository;
        $this->productOptionFactory = $productOptionFactory;
    }

    /**
     * Find or create a ProductOption
     * @param string $code
     * @return ProductOption
     */
    public function provide(string $code): ProductOption
    {
        /** @var ProductOption|null $option */
        $option = $this->productOptionRepository->findOneBy([
            'code' => $code
        ]);

        if (null === $option) {
            /** @var ProductOption $option */
            $option = $this->productOptionFactory->createNew();
            $option->setCode($code);
            $option->setName($code);
            $option->setPosition(0);
        }

        return $option;
    }
}
