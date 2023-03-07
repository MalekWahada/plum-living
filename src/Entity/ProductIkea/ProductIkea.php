<?php

declare(strict_types=1);

namespace App\Entity\ProductIkea;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="product_ikea", indexes={
 *     @ORM\Index(name="product_ikea_code", columns={"code"})
 * })
 * @UniqueEntity(
 *     fields={"code"},
 *     groups={"sylius", "Default"}
 * )
 */
class ProductIkea implements ResourceInterface, TranslatableInterface, CodeAwareInterface, ImageAwareInterface
{
    use TimestampableTrait, ToggleableTrait;
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->createdAt = new DateTime();
        $this->channelPricings = new ArrayCollection();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $code = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ProductIkea\ProductIkeaImage", mappedBy="owner", orphanRemoval=true, cascade={"all"})
     */
    protected ?ImageInterface $image = null;

    /**
     * @var Collection|ProductIkeaChannelPricing[]
     * @ORM\OneToMany(targetEntity=ProductIkeaChannelPricing::class, mappedBy="productIkea", indexBy="channelCode", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="product_ikea_id", referencedColumnName="id", nullable=false)
     */
    protected Collection $channelPricings;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(?ImageInterface $image): void
    {
        if (null !== $image) {
            $image->setOwner($this);
        }
        $this->image = $image;
    }

    public function setName(?string $name): void
    {
        $this->getTranslation()->setName($name);
    }

    public function getName(): ?string
    {
        return $this->getTranslation()->getName();
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslation(): TranslationInterface
    {
        return new ProductIkeaTranslation();
    }

    /**
     * @return ProductIkeaTranslation
     */
    public function getTranslation(?string $locale = null): TranslationInterface
    {
        /** @var ProductIkeaTranslation $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function getChannelPricings(): Collection
    {
        return $this->channelPricings;
    }

    public function getChannelPricingForChannel(ChannelInterface $channel): ?ProductIkeaChannelPricing
    {
        $items = $this->channelPricings->filter(
            fn (ProductIkeaChannelPricing $channelPricing) => $channel->getCode() === $channelPricing->getChannelCode()
        );

        return $items->isEmpty() ? null : $items->first();
    }

    public function hasChannelPricingForChannel(ChannelInterface $channel): bool
    {
        return null !== $this->getChannelPricingForChannel($channel);
    }

    public function hasChannelPricing(ProductIkeaChannelPricing $channelPricing): bool
    {
        return $this->channelPricings->contains($channelPricing);
    }

    public function addChannelPricing(ProductIkeaChannelPricing $channelPricing): void
    {
        if ($this->channelPricings->contains($channelPricing)) {
            return;
        }
        $channelPricing->setProductIkea($this);
        $this->channelPricings[] = $channelPricing;
    }

    public function removeChannelPricing(ProductIkeaChannelPricing $channelPricing): void
    {
        if (!$this->channelPricings->contains($channelPricing)) {
            return;
        }
        $this->channelPricings->removeElement($channelPricing);
        $channelPricing->setProductIkea(null);
    }
}
