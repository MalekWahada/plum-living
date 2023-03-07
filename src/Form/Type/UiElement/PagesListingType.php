<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Entity\Page\Page;
use App\Provider\CMS\Page\PageProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class PagesListingType extends AbstractType
{
    private PageProvider $pageProvider;

    public function __construct(PageProvider $pageProvider)
    {
        $this->pageProvider = $pageProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pageTypes = [
            Page::PAGE_TYPE_PROJECT,
            Page::PAGE_TYPE_INSPIRATION,
            Page::PAGE_TYPE_ARTICLE,
            Page::PAGE_TYPE_MEDIA_ARTICLE,
        ];

        $pages = $this->pageProvider->getPagesByType($pageTypes);
        $pagesCodes = [];
        foreach ($pages as $page) {
            $pagesCodes[$page->getType()][] = $page->getCode();
        }

        $builder->add('pages', ChoiceType::class, [
            'label' => 'app.form.pages_listing.label',
            'choices' => $pagesCodes,
            'multiple' => true,
            'constraints' => [
                new Assert\NotBlank([]),
            ],
            'choice_label' => fn (string $pageCode): string => current(array_filter(
                $pages,
                static fn (Page $page): bool => $page->getCode() === $pageCode
            ))->getTitle(),
        ]);
    }
}
