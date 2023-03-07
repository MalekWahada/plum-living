<?php

declare(strict_types=1);

namespace App\Entity\CustomerProject;

use App\Broker\PlumScannerApiClient;
use App\Entity\Channel\Channel;
use App\Entity\Customer\Customer;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="customer_project")
 */
class Project implements ResourceInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $name;

    /**
     * @ORM\Column(name="token", type="string", length=64)
     */
    protected string $token;

    /**
     * @ORM\Column(name="scanner_project_id", type="string", length=64, nullable=true)
     */
    protected ?string $scannerProjectId = null;

    /**
     * @ORM\Column(name="scanner_transfer_email", type="string", length=255, nullable=true)
     */
    protected ?string $scannerTransferEmail = null;

    /**
     * Check whether project details are already fetched from Plum Scanner API or not
     *
     * @var bool
     * @ORM\Column(name="scanner_fetched", type="boolean")
     */
    protected bool $scannerFetched = false;

    /**
     * @ORM\Column(name="scanner_status", type="string", length=255)
     */
    protected string $scannerStatus = PlumScannerApiClient::STATUS_WAITING_FOR_EMAIL;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Taxonomy\Taxon")
     * @ORM\JoinColumn(nullable=false, name="facade", referencedColumnName="id", nullable=true)
     */
    protected ?Taxon $facade = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     * @ORM\JoinColumn(nullable=true, name="option_value_design", referencedColumnName="id", nullable=true)
     */
    protected ?ProductOptionValue $design = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     * @ORM\JoinColumn(nullable=true, name="option_value_finish", referencedColumnName="id", nullable=true)
    */
    protected ?ProductOptionValue $finish = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     * @ORM\JoinColumn(nullable=true, name="option_value_color", referencedColumnName="id", nullable=true)
     */
    protected ?ProductOptionValue $color = null;

    /**
     * @var Collection|ProjectItem[]
     * @ORM\OneToMany(
     *     targetEntity="ProjectItem",
     *     mappedBy="project",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected Collection $items;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="projects")
     */
    protected Customer $customer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $comment = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getScannerProjectId(): ?string
    {
        return $this->scannerProjectId;
    }

    public function setScannerProjectId(?string $scannerProjectId): void
    {
        $this->scannerProjectId = $scannerProjectId;
    }

    public function getScannerTransferEmail(): ?string
    {
        return $this->scannerTransferEmail;
    }

    public function setScannerTransferEmail(?string $scannerTransferEmail): void
    {
        $this->scannerTransferEmail = $scannerTransferEmail;
    }

    public function isScannerFetched(): bool
    {
        return $this->scannerFetched;
    }

    public function setScannerFetched(bool $scannerFetched): void
    {
        $this->scannerFetched = $scannerFetched;
    }

    public function getScannerStatus(): string
    {
        return $this->scannerStatus;
    }

    public function setScannerStatus(string $scannerStatus): void
    {
        $this->scannerStatus = $scannerStatus;
    }

    public function getFacade(): ?Taxon
    {
        return $this->facade;
    }

    public function setFacade(?Taxon $facade): void
    {
        $this->facade = $facade;
    }

    public function getDesign(): ?ProductOptionValue
    {
        return $this->design;
    }

    public function setDesign(?ProductOptionValue $design): void
    {
        $this->design = $design;
    }

    public function getFinish(): ?ProductOptionValue
    {
        return $this->finish;
    }

    public function setFinish(?ProductOptionValue $finish): void
    {
        $this->finish = $finish;
    }

    public function getColor(): ?ProductOptionValue
    {
        return $this->color;
    }

    public function setColor(?ProductOptionValue $color): void
    {
        $this->color = $color;
    }

    /**
     * @return Collection|ProjectItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ProjectItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setProject($this);
        }
    }

    public function removeItem(ProjectItem $item): void
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $item->setProject(null);
        }
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getTotalPrice(Channel $channel): int
    {
        $totalPrice = 0;

        foreach ($this->items as $item) {
            $totalPrice += $item->getTotalPrice($channel);
        }

        return $totalPrice;
    }
}
