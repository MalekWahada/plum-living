<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use App\Entity\Addressing\Country;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_channel")
 */
class Channel extends BaseChannel
{
    public const DEFAULT_CODE = 'PLUM_FR';

    /**
     * @var Country|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Addressing\Country")
     * @ORM\JoinColumn(name="default_country_id", referencedColumnName="id", nullable=false, unique=true)
     */
    private ?Country $defaultCountry = null;

    public function getDefaultCountry(): ?Country
    {
        return $this->defaultCountry;
    }

    public function setDefaultCountry(?Country $defaultCountry): void
    {
        $this->defaultCountry = $defaultCountry;
    }
}
