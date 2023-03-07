<?php

declare(strict_types=1);

namespace App\Entity\Tunnel\Shopping;

use App\Repository\CombinationImageRepository;
use Sylius\Component\Core\Model\Image;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="facade_combination_image")
 */
class CombinationImage extends Image
{
    /**
     *
     * @var Combination
     *
     * @ORM\OneToOne(targetEntity=Combination::class, inversedBy="image")
     * @ORM\JoinColumn(nullable=false, name="combination_id", referencedColumnName="id")
     */
    protected $owner;
}
