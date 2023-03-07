<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Action;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Entity\Order\OrderItemUnit;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxonomy\Taxon;
use App\Promotion\Action\OrderPercentageOfFrontItemsDiscountCommand;
use App\Promotion\Applicator\FixedDiscountPromotionAdjustmentApplicator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

/**
 * @covers \App\Promotion\Action\OrderPercentageOfFrontItemsDiscountCommand
 */
final class OrderPercentageOfFrontItemsDiscountCommandTest extends TestCase
{
    /** @var MockObject&AdjustmentFactoryInterface */
    private $adjustmentFactory;

    /** @var MockObject&FixedDiscountPromotionAdjustmentApplicator */
    private $fixedDiscountPromotionAdjustmentApplicator;

    protected function setUp(): void
    {
        $this->adjustmentFactory = $this->createMock(AdjustmentFactoryInterface::class);
        $this->fixedDiscountPromotionAdjustmentApplicator =
            $this->createMock(FixedDiscountPromotionAdjustmentApplicator::class);

        parent::setUp();
    }

    public function testItSkipsWhenWeDoNotApplyOnOrder(): void
    {
        $b2bDiscountCommand = new OrderPercentageOfFrontItemsDiscountCommand(
            $this->fixedDiscountPromotionAdjustmentApplicator
        );

        $this->fixedDiscountPromotionAdjustmentApplicator->expects(self::never())
            ->method('apply');

        $notAnOrder = new class implements PromotionSubjectInterface {
            public function getPromotionSubjectTotal(): int
            {
                return 0;
            }

            public function getPromotions(): Collection
            {
                return new ArrayCollection();
            }

            public function hasPromotion(\Sylius\Component\Promotion\Model\PromotionInterface $promotion): bool
            {
                return true;
            }

            public function addPromotion(\Sylius\Component\Promotion\Model\PromotionInterface $promotion): void
            {
            }

            public function removePromotion(\Sylius\Component\Promotion\Model\PromotionInterface $promotion): void
            {
            }
        };

        self::assertFalse(
            $b2bDiscountCommand->execute($notAnOrder, [], $this->createStub(PromotionInterface::class))
        );
    }

    public function testItCalculatesReductionFromFrontProducts(): void
    {
        $promotion = $this->createStub(PromotionInterface::class);
        $this->adjustmentFactory->expects(self::never())
            ->method('createNew');

        $b2bDiscountCommand = new OrderPercentageOfFrontItemsDiscountCommand(
            $this->fixedDiscountPromotionAdjustmentApplicator
        );

        $order = new Order();
        $itemWithoutProduct = new OrderItem();
        $itemWithoutProductVariant = new ProductVariant();
        $itemWithoutProduct->setVariant($itemWithoutProductVariant);
        $order->addItem($itemWithoutProduct);

        $order->addItem($this->createItemForOrder(100, Taxon::TAXON_FACADE_CODE));
        $order->addItem($this->createItemForOrder(200, Taxon::TAXON_FACADE_CODE));
        $order->addItem($this->createItemForOrder(666, Taxon::TAXON_ACCESSORY_CODE));

        $this->fixedDiscountPromotionAdjustmentApplicator->expects(self::once())
            ->method('apply')
            ->with($order, $promotion, -30);

        $b2bDiscountCommand->execute($order, ['percentage' => 0.1], $promotion);
        self::assertSame(0, $order->getAdjustmentsTotalRecursively());
        self::assertSame(966, $order->getTotal());
    }

    private function createItemForOrder(int $unitPrice, string $taxonCode): OrderItem
    {
        $orderItem = new OrderItem();
        $orderItem->setUnitPrice($unitPrice);

        $taxon = new Taxon();
        $taxon->setCode($taxonCode);

        $product = new Product();
        $product->setMainTaxon($taxon);

        $productVariant = new ProductVariant();
        $productVariant->setProduct($product);

        new OrderItemUnit($orderItem);

        $orderItem->setVariant($productVariant);

        return $orderItem;
    }
}
