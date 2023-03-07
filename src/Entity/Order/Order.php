<?php

declare(strict_types=1);

namespace App\Entity\Order;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Taxonomy\Taxon;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Noksi\SyliusPlumHubspotPlugin\Contract\CrmEntityInterface;
use Noksi\SyliusPlumHubspotPlugin\Traits\CrmEntityTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Core\Model\PaymentInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 *
 * @method OrderItem[]|Collection getItems()
 * @ApiFilter(ExistsFilter::class, properties={"erpRegistered"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "updatedAt"})
 * @ApiFilter(SearchFilter::class, properties={"paymentState": "exact"})
 */
class Order extends BaseOrder implements CrmEntityInterface
{
    use CrmEntityTrait;

    // rooms list
    public const TARGET_ROOM_KITCHEN = 'kitchen';
    public const TARGET_ROOM_SALOON = 'saloon';
    public const TARGET_ROOM_ADULT_BEDROOM = 'adult-bedroom';
    public const TARGET_ROOM_CHILDREN_BEDROOM = 'children-bedroom';
    public const TARGET_ROOM_BATHROOM = 'bathroom';
    public const TARGET_ROOM_OFFICE = 'office';
    public const TARGET_ROOM_ENTRY = 'entry';
    public const TARGET_ROOM_OTHER = 'other';

    // mailing workflow type list
    public const MAILING_WORKFLOW_TYPE_METHOD_AND_PAX = 'method-and-pax-mailing';
    public const MAILING_WORKFLOW_TYPE_METHOD = 'method-mailing';
    public const MAILING_WORKFLOW_TYPE_PAX = 'pax-mailing';
    public const MAILING_WORKFLOW_TYPE_NOT_FRONT = 'not-front-mailing';

    // erp status
    public const DEFAULT_ERP_STATUS = 'new';

    /**
     * @ORM\Column(name="original_order_number", type="string", length=255, nullable=true)
     */
    protected ?string $originalOrderNumber = null;

    /**
     * @ORM\Column(name="targeted_room", type="string", length=32, nullable=true)
     */
    protected ?string $targetedRoom = null;

    /**
     * @ORM\Column(name="min_date_delivery", type="date", nullable=true)
     */
    protected ?DateTime $minDateDelivery = null;

    /**
     * @ORM\Column(name="max_date_delivery", type="date", nullable=true)
     */
    protected ?DateTime $maxDateDelivery = null;

    /**
     * @ORM\Column(name="mailing_workflow_type", length=32, type="string", nullable=true)
     */
    protected ?string $mailingWorkflowType = null;

    /**
     * @ORM\Column(name="erp_registered", type="integer", nullable=true)
     */
    private ?int $erpRegistered = null;

    /**
     * @ORM\Column(name="erp_status",type="string", length=50)
     */
    private string $erpStatus = self::DEFAULT_ERP_STATUS;

    public function getMinDateDelivery(): ?DateTime
    {
        return $this->minDateDelivery;
    }

    public function setMinDateDelivery(?DateTime $minDateDelivery): void
    {
        $this->minDateDelivery = $minDateDelivery;
    }

    public function getMaxDateDelivery(): ?DateTime
    {
        return $this->maxDateDelivery;
    }

    public function setMaxDateDelivery(?DateTime $maxDateDelivery): void
    {
        $this->maxDateDelivery = $maxDateDelivery;
    }

    public function getOriginalOrderNumber(): ?string
    {
        return $this->originalOrderNumber;
    }

    public function setOriginalOrderNumber(?string $originalOrderNumber): void
    {
        $this->originalOrderNumber = $originalOrderNumber;
    }

    public function getTargetedRoom(): ?string
    {
        return $this->targetedRoom;
    }

    public function setTargetedRoom(?string $targetedRoom): void
    {
        $this->targetedRoom = $targetedRoom;
    }

    public function getMailingWorkflowType(): ?string
    {
        return $this->mailingWorkflowType;
    }

    public function setMailingWorkflowType(?string $mailingWorkflowType): void
    {
        $this->mailingWorkflowType = $mailingWorkflowType;
    }

    public function needsConfirmationModal(): bool
    {
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $taxons = $product->getTaxons();
            foreach ($taxons as $taxon) {
                if ($taxon->isChildOf(Taxon::TAXON_FACADE_CODE)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return an array of items' quantities per taxon. Custom taxons could be returned as drawer, plinth / panels and doors are grouped together.
     *
     * @return array
     */
    public function getItemsCountPerTaxon(): array
    {
        $counters = [];

        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $taxons = $product->getTaxons();

            foreach ($taxons as $taxon) {
                if (!$taxon->hasChildren() || Taxon::TAXON_ACCESSORY_CODE === $taxon->getCode()) {
                    switch (true) {
                        case in_array($taxon->getCode(), Taxon::CUSTOM_TAXONS_PANEL_AND_PLINTH_CODES, true):
                            $taxonCode = Taxon::CUSTOM_TAXON_PANEL_AND_PLINTH_CODE;
                            break;
                        case in_array($taxon->getCode(), Taxon::CUSTOM_TAXONS_DOOR_CODES, true):
                            $taxonCode = Taxon::CUSTOM_TAXON_DOOR_CODE;
                            break;
                        case in_array($taxon->getCode(), Taxon::CUSTOM_TAXONS_DRAWER_CODES, true):
                            $taxonCode = Taxon::CUSTOM_TAXON_DRAWER_CODE;
                            break;
                        default:
                            $taxonCode = $taxon->getCode();
                    }

                    if (!array_key_exists($taxonCode, $counters)) {
                        $counters[$taxonCode] = 0;
                    }

                    $counters[$taxonCode] += $item->getQuantity();
                    break;
                }
            }
        }

        return $counters;
    }

    public static function targetedRoomsList(): array
    {
        return [
            self::TARGET_ROOM_KITCHEN,
            self::TARGET_ROOM_SALOON,
            self::TARGET_ROOM_ADULT_BEDROOM,
            self::TARGET_ROOM_CHILDREN_BEDROOM,
            self::TARGET_ROOM_BATHROOM,
            self::TARGET_ROOM_OFFICE,
            self::TARGET_ROOM_ENTRY,
            self::TARGET_ROOM_OTHER,
        ];
    }

    public function getErpRegistered(): ?int
    {
        return $this->erpRegistered;
    }

    public function setErpRegistered(?int $erpRegistered): void
    {
        $this->erpRegistered = $erpRegistered;
    }

    public function hasItemType(string $taxonCode): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->isType($taxonCode)) {
                return true;
            }
        }
        return false;
    }

    public function getItemTypeTotalAmount(string $taxonCode): int
    {
        $totalAmount = 0;
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->isType($taxonCode)) {
                $totalAmount += $item->getTotal();
            }
        }
        return $totalAmount;
    }

    public function hasFacadeItem(): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->isFacade()) {
                return true;
            }
        }

        return false;
    }

    public function hasFacadeSampleItem(): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->isFacadeSample()) {
                return true;
            }
        }

        return false;
    }

    public function hasPaintItem(): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->isPaint()) {
                return true;
            }
        }

        return false;
    }

    public function hasPaintSampleItem(): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->isPaintSample()) {
                return true;
            }
        }

        return false;
    }

    public function getErpStatus(): string
    {
        return $this->erpStatus;
    }

    public function setErpStatus(string $erpStatus): void
    {
        $this->erpStatus = $erpStatus;
    }

    public function getPaymentsByState(?string $paymentState = null): Collection
    {
        if (null === $paymentState) {
            return $this->payments;
        }

        return $this->payments->filter(function (PaymentInterface $payment) use ($paymentState) {
            return $paymentState === $payment->getState();
        });
    }
}
