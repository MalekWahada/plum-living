<?php

declare(strict_types=1);

namespace App\Controller\Inspiration;

use App\Entity\Page\Page;
use App\Form\Type\CMSFilters\ListingInspirationFilterType;
use App\Model\CMSFilter\ListingInspirationFilterModel;
use App\Provider\CMS\Page\PageProvider;
use App\Provider\CMS\UI\UICodesProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class InspirationController
{
    private Environment $twig;
    private PageProvider $pageProvider;
    private FormFactoryInterface $formFactory;
    private UICodesProvider $codesProvider;

    public function __construct(
        Environment $twig,
        PageProvider $pageProvider,
        FormFactoryInterface $formFactory,
        UICodesProvider $codesProvider
    ) {
        $this->pageProvider = $pageProvider;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->codesProvider = $codesProvider;
    }

    public function index(Request $request): Response
    {
        $listingInspirationFilterModel = new ListingInspirationFilterModel();

        if (null !== $request->query->get('chip')) {
            $listingInspirationFilterModel->setChip($request->query->get('chip'));
        }

        $form = $this->formFactory->createBuilder(ListingInspirationFilterType::class, $listingInspirationFilterModel)->getForm();
        $form->handleRequest($request);

        $inspirations = $this->pageProvider->getPagesByContent(
            Page::PAGE_TYPE_ARTICLE,
            $request->getLocale(),
            $this->codesProvider->getFormattedContents($listingInspirationFilterModel)
        );

        return new Response($this->twig->render(
            'Shop/Plum/Inspiration/index.html.twig',
            [
                'inspirations' => $inspirations,
                'form' => $form->createView(),
            ]
        ));
    }
}
