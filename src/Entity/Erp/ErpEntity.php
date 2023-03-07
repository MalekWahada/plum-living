<?php

declare(strict_types=1);

namespace App\Entity\Erp;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="erp_entity",
 *     uniqueConstraints={@ORM\UniqueConstraint(columns={"type", "erp_id"})}
 * )
 */
class ErpEntity implements ResourceInterface
{
    const TYPE_PRODUCT = 'product';
    const TYPE_PRODUCT_VARIANT = 'product_variant';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product:read", "product_variant:read", "order:read"})
     */
    protected string $type;

    /**
     * @ORM\Column(name="erp_id", type="integer")
     * @Groups({"product:read", "product_variant:read", "order:read"})
     */
    protected int $erpId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product:read", "product_variant:read", "order:read"})
     */
    protected string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getErpId(): int
    {
        return $this->erpId;
    }

    public function setErpId(int $erpId): void
    {
        $this->erpId = $erpId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
