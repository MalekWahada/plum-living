<?php

declare(strict_types=1);

namespace App\Controller\Media;

use App\Entity\Page\Page;
use App\Entity\Product\ProductOptionValue;
use App\Form\Type\CMSFilters\ListingProjectFilterType;
use App\Model\CMSFilter\ListingProjectFilterModel;
use App\Provider\CMS\Chip\ChipProvider;
use App\Provider\CMS\Page\PageProvider;
use App\Repository\Product\ProductOptionValueRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class HomeProjectController extends AbstractController
{
    private ChipProvider $chipProvider;
    private FormFactoryInterface $formFactory;
    private PageProvider $pageProvider;
    private ProductOptionValueRepository $productOptionValueRepository;
    private TranslatorInterface $translator;
    private Environment $twig;
    private LocaleContextInterface $localeContext;

    public function __construct(
        ChipProvider $chipProvider,
        FormFactoryInterface $formFactory,
        PageProvider $pageProvider,
        ProductOptionValueRepository $productOptionValueRepository,
        TranslatorInterface $translator,
        Environment $twig,
        LocaleContextInterface $localeContext
    ) {
        $this->chipProvider = $chipProvider;
        $this->formFactory  = $formFactory;
        $this->pageProvider = $pageProvider;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->translator   = $translator;
        $this->twig         = $twig;
        $this->localeContext = $localeContext;
    }

    public function index(Request $request): Response
    {
        $locale = $this->localeContext->getLocaleCode();
        $limit     = 12;
        $page      = (int) $request->get('page', 1);
        if ($page < 1) {
            $page  = 1;
        }

        $rooms  = $this->chipProvider->getRooms();
        $colors = array_filter(
            $this->productOptionValueRepository->getEnabledColors(),
            static fn (ProductOptionValue $optionValue): bool => !in_array($optionValue->getCode(), ProductOptionValue::HIDDEN_COLORS, true)
        );

        $listingProjectFilterModel = new ListingProjectFilterModel();

        if (null !== $request->query->get('room')) {
            $listingProjectFilterModel->setPiece($request->query->get('room'));
        }
        if (null !== $request->query->get('color')) {
            $listingProjectFilterModel->setColor($request->query->get('color'));
        }

        $paginator = $this->getPaginatorFromCategory(
            $listingProjectFilterModel,
            $locale,
            ChipProvider::CHIP_TYPE_HOME_PROJECT,
            $page,
            $limit
        );

        return new Response(
            $this->twig->render('Shop/Plum/Project/home.html.twig', [
                'colors'      => $colors,
                'rooms'       => $rooms,
                'items'       => $paginator->getIterator(),
                'total'       => $paginator->count(),
                'currentPage' => $page,
                'totalPages'  => (int) ceil($paginator->count() / $limit),
            ])
        );
    }

    private function getPaginatorFromCategory(
        ListingProjectFilterModel $filters,
        string $_locale,
        string $category,
        int $page,
        int $limit
    ): Paginator {
        $query = $this
            ->pageProvider
            ->getBaseQueryBuilder(
                $_locale,
                Page::PAGE_TYPE_MEDIA_ARTICLE,
                $page - 1,
                $limit
            )
            ->andWhere('p.category = :category')
            ->setParameter('category', $category);
        if ($filters->getColor()) {
            $query
                ->innerJoin('p.options', 'o1', Join::WITH, 'o1.type = :type_color')
                ->andWhere('o1.value = :value_color')
                ->setParameter('type_color', 'color')
                ->setParameter('value_color', $filters->getColor());
        }
        if ($filters->getPiece()) {
            $query
                ->innerJoin('p.options', 'o2', Join::WITH, 'o2.type = :type_room')
                ->andWhere('o2.value = :value_room')
                ->setParameter('type_room', 'room')
                ->setParameter('value_room', $filters->getPiece());
        }

        return new Paginator($query, $fetchJoinCollection = true);
    }
}
