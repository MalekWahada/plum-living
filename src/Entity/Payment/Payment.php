<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Payment as BasePayment;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment")
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Column(name="wire_details", type="array")
     * @var array<string, string> $wireDetails
     */
    private array $wireDetails = [];

    /**
     * @ORM\Column(name="reminded_at", type="datetime", nullable=true)
     * @var ?\Datetime $remindedAt
     */
    private ?\DateTime $remindedAt;

    /**
     * @return array<string, string>
     */
    public function getWireDetails(): array
    {
        return $this->wireDetails;
    }

    /**
     * @param array<string, string> $wireDetails
     */
    public function setWireDetails(array $wireDetails): void
    {
        $this->wireDetails = $wireDetails;
    }

    /**
     * @return \Datetime|null
     */
    public function getRemindedAt(): ?\DateTime
    {
        return $this->remindedAt;
    }

    /**
     * @param \Datetime|null $remindedAt
     */
    public function setRemindedAt(?\DateTime $remindedAt): void
    {
        $this->remindedAt = $remindedAt;
    }
}
