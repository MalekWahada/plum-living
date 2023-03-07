<?php

declare(strict_types=1);

namespace App\Controller\CustomerProject;

use App\EmailManager\CMS\ProjectPlanEmailManager;
use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Entity\Page\Page;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use App\Factory\CustomerProject\ProjectFactoryInterface;
use App\Form\Type\CMSFilters\ListingProjectFilterType;
use App\Model\CMSFilter\ListingProjectFilterModel;
use App\Provider\CMS\Page\PageProvider;
use App\Provider\CMS\PageCode\PageCodeProvider;
use App\Provider\CMS\UI\UICodesProvider;
use App\Provider\Product\ProductOptionValueProvider;
use App\Provider\User\ShopUserProvider;
use App\Repository\CustomerProject\ProjectRepository;
use App\Repository\Product\ProductOptionValueRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountProjectController extends AbstractController
{
    private PageProvider $pageProvider;
    private ProjectPlanEmailManager $projectPlanEmailManager;
    private FormFactoryInterface $formFactory;
    private RouterInterface $router;
    private UICodesProvider $codesProvider;
    private TranslatorInterface $translator;
    private ProjectRepository $projectRepository;
    private FlashBagInterface $flashBag;
    private ShopUserProvider $shopUserProvider;
    private ProjectFactoryInterface $projectFactory;
    private ProductOptionValueProvider $productOptionValueProvider;
    private ProductOptionValueRepository $productOptionValueRepository;

    public function __construct(
        PageProvider $pageProvider,
        ProjectPlanEmailManager $projectPlanEmailManager,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UICodesProvider $codesProvider,
        TranslatorInterface $translator,
        ProjectRepository $projectRepository,
        FlashBagInterface $flashBag,
        ShopUserProvider $shopUserProvider,
        ProjectFactoryInterface $projectFactory,
        ProductOptionValueProvider $productOptionValueProvider,
        ProductOptionValueRepository $productOptionValueRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->flashBag = $flashBag;
        $this->shopUserProvider = $shopUserProvider;
        $this->projectFactory = $projectFactory;
        $this->productOptionValueProvider = $productOptionValueProvider;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->pageProvider = $pageProvider;
        $this->projectPlanEmailManager = $projectPlanEmailManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->codesProvider = $codesProvider;
        $this->translator = $translator;
    }

    public function index(Request $request): Response
    {
        $listingProjectFilterModel = new ListingProjectFilterModel();

        if ($request->query->has('piece')) {
            $listingProjectFilterModel->setPiece($request->query->get('piece'));
        }
        $form = $this->formFactory->createBuilder(ListingProjectFilterType::class, $listingProjectFilterModel)->getForm();
        $form->handleRequest($request);

        $projects = $this->pageProvider->getPagesByContent(
            Page::PAGE_TYPE_PROJECT,
            $request->getLocale(),
            $this->codesProvider->getFormattedContents($listingProjectFilterModel)
        );
        $page = $this->pageProvider->getPageByTypeAndCode(Page::PAGE_TYPE_DEFAULT, PageCodeProvider::PAGE_DESIGN_FINISH_CODE);
        $link = $page !== null ? $page->getSlug() : null;

        return $this->render(
            'Shop/Plum/Project/index.html.twig',
            [
                'projects' => $projects,
                'form' => $form->createView(),
                'link' => $link,
            ]
        );
    }

    public function list(): Response
    {
        $shopUser = $this->shopUserProvider->getShopUser();

        if (null === $shopUser) {
            return new Response('Not granted', Response::HTTP_FORBIDDEN);
        }

        /** @var Customer $customer */
        $customer = $shopUser->getCustomer();

        return $this->render(
            'Shop/Account/Project/list.html.twig',
            [
                'projects' => $this->projectRepository->getCustomerProjects($customer)
            ]
        );
    }

    public function delete(string $token): Response
    {
        /** @var Project|null $project */
        $project = $this->projectRepository->findOneBy(['token' => $token]);

        if (null === $project) {
            return new Response('Not found', Response::HTTP_NOT_FOUND);
        }

        $shopUser = $this->shopUserProvider->getShopUser();

        if (null === $shopUser) {
            return new Response('Not granted', Response::HTTP_FORBIDDEN);
        }

        /** @var Customer $customer */
        $customer = $shopUser->getCustomer();

        if ($customer !== $shopUser->getCustomer()) {
            return new Response('Not granted', Response::HTTP_FORBIDDEN);
        }

        $this->projectRepository->remove($project);

        $this->flashBag->add('success', 'app.project.delete_project_success');

        return new RedirectResponse($this->router->generate('app_account_project_list'));
    }

    /**
     * @throws Exception
     */
    public function duplicate(string $token): Response
    {
        /** @var Project|null $project */
        $project = $this->projectRepository->findOneBy(['token' => $token]);

        if (null === $project) {
            throw new NotFoundHttpException();
        }

        $shopUser = $this->shopUserProvider->getShopUser();
        if (null === $shopUser) {
            return  new Response('Not granted', Response::HTTP_FORBIDDEN);
        }

        /** @var Customer $customer */
        $customer = $shopUser->getCustomer();

        if ($customer !== $project->getCustomer()) {
            return new Response('Not granted', Response::HTTP_FORBIDDEN);
        }

        $clone = $this->projectFactory->createForClone($project);
        $clone = $this->projectFactory->createItemsForClonedProject($project, $clone);

        $this->projectRepository->add($clone);

        $this->flashBag->add('success', 'app.project.duplicate_project_success');

        return new RedirectResponse($this->router->generate('app_account_project_list'));
    }

    public function create(): Response
    {
        $shopUser = $this->shopUserProvider->getShopUser();
        if (null === $shopUser) {
            return  new Response('Not granted', Response::HTTP_FORBIDDEN);
        }

        /** @var Customer $customer */
        $customer = $shopUser->getCustomer();

        $project = $this->projectFactory->createNew();
        $project->setCustomer($customer);
        $project->setName(sprintf(
            '%s #%d',
            $this->translator->trans('app.ui.generic.project'),
            $this->projectRepository->getLastId() + 1
        ));

        if ($facadeType = $this->productOptionValueProvider->findFacadeType(Taxon::TAXON_FACADE_METOD)) {
            $project->setFacade($facadeType);
        }

        if ($design = $this->productOptionValueRepository->findOneBy(
            ['code' => ProductOptionValue::DESIGN_STRAIGHT_CODE]
        )
        ) {
            $project->setDesign($design);
        }

        if ($finish = $this->productOptionValueRepository->findOneBy(
            ['code' => ProductOptionValue::FINISH_LACQUER_MATT_CODE]
        )
        ) {
            $project->setFinish($finish);
        }

        if ($color = $this->productOptionValueRepository->findOneBy(['code' => 'color_bleu_lagon'])) {
            $project->setColor($color);
        }

        $this->projectRepository->add($project);

        $this->flashBag->add('success', 'app.project.create_project_success');

        return new RedirectResponse(
            $this->router->generate(
                'app_customer_project_show',
                ['token' => $project->getToken()]
            )
        );
    }
}
