<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use App\Entity\CustomerProject\Project;
use App\Entity\Locale\Locale;
use App\Entity\Promotion\PromotionCoupon;
use App\Provider\Customer\CustomerTypeChoicesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Noksi\SyliusPlumHubspotPlugin\Contract\CrmEntityInterface;
use Noksi\SyliusPlumHubspotPlugin\Traits\CrmEntityTrait;
use Sylius\Component\Core\Model\Customer as BaseCustomer;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_customer")
 */
class Customer extends BaseCustomer implements CrmEntityInterface
{
    use CrmEntityTrait;

    /**
     * @ORM\Column(name="locale_code", type="string", length=12, options={ "default": Locale::DEFAULT_LOCALE_CODE })
     */
    protected ?string $localeCode = Locale::DEFAULT_LOCALE_CODE;

    /**
     * @ORM\Column(name="channel_code", type="string", length=255, nullable=true)
     */
    protected ?string $channelCode = null;

    /**
     * @ORM\Column(name="plum_scanner_user_id", type="string", length=255, nullable=true)
     */
    private ?string $plumScannerUserId;

    /**
     * @var Collection|Project[]
     * @ORM\OneToMany(targetEntity="App\Entity\CustomerProject\Project", mappedBy="customer")
     */
    private Collection $projects;

    /**
     * @ORM\Column(name="how_you_know_about_us", type="string", length=255, nullable=true)
     */
    private ?string $howYouKnowAboutUs = null;

    /**
     * @ORM\Column(name="customer_type", type="string", length=255, nullable=true)
     */
    private ?string $customerType = null;

    /**
     * @ORM\Column(name="how_you_know_about_us_details", type="text", nullable=true)
     */
    private ?string $howYouKnowAboutUsDetails = null;

    /**
     * B2B Program is the internal name for Terra Club feature
     * @ORM\Column(name="b2b_program", type="boolean")
     */
    private bool $b2bProgram = false;

    /**
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    private ?string $companyName = null;

    /**
     * @ORM\Column(name="company_instagram", type="string", length=255, nullable=true)
     */
    private ?string $companyInstagram = null;

    /**
     * @ORM\Column(name="company_website", type="string", length=255, nullable=true)
     */
    private ?string $companyWebsite = null;

    /**
     * @ORM\Column(name="company_street", type="string", length=255, nullable=true)
     */
    private ?string $companyStreet = null;

    /**
     * @ORM\Column(name="company_postcode", type="string", length=255, nullable=true)
     */
    private ?string $companyPostcode = null;

    /**
     * @ORM\Column(name="company_city", type="string", length=255, nullable=true)
     */
    private ?string $companyCity = null;

    /**
     * @ORM\Column(name="company_country_code", type="string", length=2, nullable=true)
     */
    private ?string $companyCountryCode = null;

    /**
     * @ORM\Column(name="vat_number", type="string", length=255, nullable=true)
     */
    private ?string $vatNumber = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Promotion\PromotionCoupon", mappedBy="customer")
     */
    private ?PromotionCoupon $personalCoupon = null;

    public function __construct()
    {
        parent::__construct();

        $this->projects = new ArrayCollection();
    }

    public function getLocaleCode(): ?string
    {
        return $this->localeCode;
    }

    public function setLocaleCode(?string $localeCode): void
    {
        Assert::string($localeCode);

        $this->localeCode = $localeCode;
    }

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }

    public function setChannelCode(?string $countryCode): void
    {
        Assert::string($countryCode);

        $this->channelCode = $countryCode;
    }

    public function getPlumScannerUserId(): ?string
    {
        return $this->plumScannerUserId;
    }

    public function setPlumScannerUserId(?string $plumScannerUserId): void
    {
        $this->plumScannerUserId = $plumScannerUserId;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
        }

        return $this;
    }

    public function getHowYouKnowAboutUs(): ?string
    {
        return $this->howYouKnowAboutUs;
    }

    public function setHowYouKnowAboutUs(?string $howYouKnowAboutUs): void
    {
        $this->howYouKnowAboutUs = $howYouKnowAboutUs;
    }

    public function getCustomerType(): ?string
    {
        return $this->customerType;
    }

    public function setCustomerType(?string $customerType): void
    {
        $this->customerType = $customerType;
    }

    public function getHowYouKnowAboutUsDetails(): ?string
    {
        return $this->howYouKnowAboutUsDetails;
    }

    public function setHowYouKnowAboutUsDetails(?string $howYouKnowAboutUsDetails): void
    {
        $this->howYouKnowAboutUsDetails = $howYouKnowAboutUsDetails;
    }

    public function hasB2BProgram(): bool
    {
        return $this->b2bProgram;
    }

    public function setB2BProgram(bool $b2bProgram): void
    {
        $this->b2bProgram = $b2bProgram;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function getCompanyInstagram(): ?string
    {
        return $this->companyInstagram;
    }

    public function setCompanyInstagram(?string $companyInstagram): void
    {
        $this->companyInstagram = $companyInstagram;
    }

    public function getCompanyWebsite(): ?string
    {
        return $this->companyWebsite;
    }

    public function setCompanyWebsite(?string $companyWebsite): void
    {
        $this->companyWebsite = $companyWebsite;
    }

    public function getCompanyStreet(): ?string
    {
        return $this->companyStreet;
    }

    public function setCompanyStreet(?string $companyStreet): void
    {
        $this->companyStreet = $companyStreet;
    }

    public function getCompanyPostcode(): ?string
    {
        return $this->companyPostcode;
    }

    public function setCompanyPostcode(?string $companyPostcode): void
    {
        $this->companyPostcode = $companyPostcode;
    }

    public function getCompanyCity(): ?string
    {
        return $this->companyCity;
    }

    public function setCompanyCity(?string $companyCity): void
    {
        $this->companyCity = $companyCity;
    }


    public function getCompanyCountryCode(): ?string
    {
        return $this->companyCountryCode;
    }

    public function setCompanyCountryCode(?string $companyCountryCode): void
    {
        $this->companyCountryCode = $companyCountryCode;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    public function getPersonalCoupon(): ?PromotionCoupon
    {
        return $this->personalCoupon;
    }

    public function setPersonalCoupon(?PromotionCoupon $personalCoupon): void
    {
        $this->personalCoupon = $personalCoupon;
    }

    /**
     * Returns if a customer is or can be a B2B. Customer type must be in B2B choices.
     * A customer can be a B2B without applying to the B2B Program
     * @return bool
     */
    public function hasB2bProgramOrIsEligibleToB2bProgram(): bool
    {
        return
            $this->hasB2BProgram() ||
            \in_array(
                $this->getCustomerType(),
                CustomerTypeChoicesProvider::CHOICES_B2B,
                true
            );
    }
}
