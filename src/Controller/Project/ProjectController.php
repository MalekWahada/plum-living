<?php

declare(strict_types=1);

namespace App\Controller\Project;

use App\EmailManager\CMS\ProjectPlanEmailManager;
use App\Entity\Page\Page;
use App\Form\Type\CMSFilters\ListingProjectFilterType;
use App\Model\CMSFilter\ListingProjectFilterModel;
use App\Provider\CMS\Page\PageProvider;
use App\Provider\CMS\PageCode\PageCodeProvider;
use App\Provider\CMS\UI\UICodesProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ProjectController extends AbstractController
{
    private PageProvider $pageProvider;
    private Environment $twig;
    private ProjectPlanEmailManager $projectPlanEmailManager;
    private FormFactoryInterface $formFactory;
    private RouterInterface $router;
    private UICodesProvider $codesProvider;
    private TranslatorInterface $translator;

    public function __construct(
        Environment $twig,
        PageProvider $pageProvider,
        ProjectPlanEmailManager $projectPlanEmailManager,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UICodesProvider $codesProvider,
        TranslatorInterface $translator
    ) {
        $this->pageProvider = $pageProvider;
        $this->twig = $twig;
        $this->projectPlanEmailManager = $projectPlanEmailManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->codesProvider = $codesProvider;
        $this->translator = $translator;
    }

    public function index(Request $request): Response
    {
        $listingProjectFilterModel = new ListingProjectFilterModel();

        if (null !== $request->query->get('piece')) {
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

        $content = $this->twig->render(
            'Shop/Plum/Project/index.html.twig',
            [
                'projects' => $projects,
                'form' => $form->createView(),
                'link' => $link,
            ]
        );

        return new Response($content);
    }

    protected function getForm(string $token): FormInterface
    {
        return $this->createFormBuilder(null, [
            'csrf_token_id' => $token,
            'csrf_field_name' => '_csrf_token',
            'allow_extra_fields' => true
        ])->getForm();
    }

    public function sendPlan(Request $request): JsonResponse
    {
        $email = $request->request->get('mail');
        $pageCode = $request->request->get('page');

        // csrf protection
        $form = $this->getForm($pageCode);
        $form->submit($request->request->all());
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new BadRequestHttpException('Invalid token');
        }

        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Not an ajax request');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException('Email not valid:' . $email);
        }
        if (!$pageCode) {
            throw new BadRequestHttpException('Page code not exists');
        }
        $page = $this->pageProvider->getPageByTypeAndCode(null, $pageCode);
        if (!$page) {
            throw new BadRequestHttpException('Page code not found:' . $pageCode);
        }
        $content = $page->getContent();
        $decoded = json_decode($content, true);
        $pdf = null;
        foreach ($decoded as $element) {
            if (!in_array($element['code'] ?? null, ['app.project_plan', 'app.media_project_plan'])) {
                continue;
            }
            $pdf = $element['data']['file'] ?? null;
        }
        if (!$pdf) {
            throw new BadRequestHttpException('PDF file not exists');
        }
        $pdfPath = $this->getParameter('sylius_core.public_dir') . $pdf;
        if (!file_exists($pdfPath)) {
            throw new BadRequestHttpException('PDF path not found:' . $pdfPath);
        }

        $this->projectPlanEmailManager->sendProjectPlan(
            $email,
            $request->getSchemeAndHttpHost() . $pdf
        );

        return new JsonResponse([
            'type' => 'success',
            'message' => $this->translator->trans('app.cms.project_plan.success', [], 'flashes'),
        ]);
    }
}
