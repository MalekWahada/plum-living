<?php

declare(strict_types=1);

namespace App\Entity\Page;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="monsieurbiz_cms_page_theme")
 */
class PageTheme implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="themes")
     */
    protected Page $page;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $theme;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function setPage(Page $page): void
    {
        $this->page = $page;
    }

    public function getTheme(): int
    {
        return $this->theme;
    }

    public function setTheme(int $theme): void
    {
        $this->theme = $theme;
    }
}
