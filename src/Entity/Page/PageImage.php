<?php

declare(strict_types=1);

namespace App\Entity\Page;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Image;

/**
 * @ORM\Entity()
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="path",
 *          column=@ORM\Column(
 *              name     = "path",
 *              type     = "string",
 *              nullable = true
 *          )
 *      )
 * })
 * @ORM\Table(name="monsieurbiz_cms_page_image")
 */
class PageImage extends Image
{
    /**
     * @var Page
     * @ORM\OneToOne(targetEntity="App\Entity\Page\Page", inversedBy="image")
     * @ORM\JoinColumn(nullable=false, name="page_id", referencedColumnName="id")
     */
    protected $owner;
}
