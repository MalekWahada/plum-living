<?php

declare(strict_types=1);

namespace App\Controller\PlumScanner;

use App\Broker\PlumScannerApiClient;
use App\Broker\PlumScannerApiClientInterface;
use App\Controller\CustomerAwareTrait;
use App\Controller\EshopControllerTrait;
use App\Dto\CustomerProject\ProjectShareUrlDto;
use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Taxonomy\Taxon;
use App\Event\PlumScanner\ScanStatusEvent;
use App\Factory\CustomerProject\ProjectFactoryInterface;
use App\Form\Type\CustomerProject\ProjectPlanType;
use App\Provider\Product\ProductOptionValueProvider;
use App\Repository\CustomerProject\ProjectRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScannerController extends AbstractController
{
    use EshopControllerTrait;
    use CustomerAwareTrait;

    private ProductOptionValueProvider $productOptionValueProvider;
    private FlashBagInterface $flashBag;
    private PlumScannerApiClientInterface $plumScannerApiClient;
    private ProjectRepository $projectRepository;
    private RouterInterface $router;
    private ProjectFactoryInterface $projectFactory;

    public function __construct(
        ProductOptionValueProvider      $productOptionValueProvider,
        FlashBagInterface               $flashBag,
        PlumScannerApiClientInterface   $plumScannerApiClient,
        ProjectRepository               $projectRepository,
        RouterInterface                 $router,
        ProjectFactoryInterface         $projectFactory
    ) {
        $this->productOptionValueProvider = $productOptionValueProvider;
        $this->flashBag = $flashBag;
        $this->plumScannerApiClient = $plumScannerApiClient;
        $this->projectRepository = $projectRepository;
        $this->router = $router;
        $this->projectFactory = $projectFactory;
    }

    public function getFacadeOption(
        Request $request,
        ?string $designCode,
        ?string $finishCode,
        ?string $colorCode
    ): Response {
        $options = $this->productOptionValueProvider->getByVariantsSuccession(
            Taxon::TAXON_FACADE_METOD,
            $designCode,
            $finishCode,
            $colorCode
        );

        if (null === $options) {
            $this->flashBag->add('error', 'app.facade.facade_not_exist');

            return $this->redirectToRoute('sylius_shop_homepage');
        }

        return $this->returnEshopViews(
            $options,
            'Shop/PlumScanner/Quote/StepOne/options.html.twig',
            $request->attributes->get('_template'),
            $request->isXmlHttpRequest()
        );
    }

    /**
     * @Entity("designOption", expr="repository.findOneByCodeAndOptionCode(designCode, 'design')")
     * @Entity("finishOption", expr="repository.findOneByCodeAndOptionCode(finishCode, 'finish')")
     * @Entity("colorOption", expr="repository.findOneByCodeAndOptionCode(colorCode, 'color')")
     * @param Request $request
     * @param ProductOptionValue $designOption
     * @param ProductOptionValue $finishOption
     * @param ProductOptionValue $colorOption
     * @return Response
     */
    public function sharePlan(
        Request $request,
        ProductOptionValue $designOption,
        ProductOptionValue $finishOption,
        ProductOptionValue $colorOption
    ): Response {
        $facadeType = $this->productOptionValueProvider->findFacadeType(Taxon::TAXON_FACADE_METOD);

        if (!$request->query->has('ikeaConnected')) {
            return $this->render('Shop/PlumScanner/Quote/StepTwo/connectIkea.html.twig', [
                'designOption' => $designOption,
                'finishOption' => $finishOption,
                'colorOption' => $colorOption,
            ]);
        }

        /** @var Customer|null $customer */
        $customer = $this->getCustomer();

        $projectDetails = $this->plumScannerApiClient->createProject($customer);

        if (null === $projectDetails) {
            $this->flashBag->add('error', 'app.plum_scanner.create_project_error');

            return $this->redirectToRoute('sylius_shop_homepage');
        }

        $project = $this->projectRepository->findOneBy(['scannerProjectId' => $projectDetails['project_id']]);

        if (null === $project) {
            $project = $this->projectFactory->createForCustomerWithOptionsAndScannerDetails(
                $customer,
                $facadeType,
                $designOption,
                $finishOption,
                $colorOption,
                [
                    'scannerTransferEmail' => $projectDetails['transfer_email'],
                    'scannerProjectId' => $projectDetails['project_id']
                ]
            );

            if (null === $project) {
                $this->flashBag->add('error', 'app.plum_scanner.create_project_error');

                return $this->redirectToRoute('sylius_shop_homepage');
            }
        }

        $this->projectRepository->add($project);

        return $this->render('Shop/PlumScanner/Quote/StepTwo/sharePlan.html.twig', [
            'designOption' => $designOption,
            'finishOption' => $finishOption,
            'colorOption' => $colorOption,
            'project' => $project,
        ]);
    }

    /**
     * @Entity("designOption", expr="repository.findOneByCodeAndOptionCode(designCode, 'design')")
     * @Entity("finishOption", expr="repository.findOneByCodeAndOptionCode(finishCode, 'finish')")
     * @Entity("colorOption", expr="repository.findOneByCodeAndOptionCode(colorCode, 'color')")
     * @param Request $request
     * @param ProductOptionValue $designOption
     * @param ProductOptionValue $finishOption
     * @param ProductOptionValue $colorOption
     * @return Response
     */
    public function sharePlanViaLink(
        Request $request,
        ProductOptionValue $designOption,
        ProductOptionValue $finishOption,
        ProductOptionValue $colorOption
    ): Response {
        $projectShareUrlDto = new ProjectShareUrlDto();
        $form = $this->createForm(ProjectPlanType::class, $projectShareUrlDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$request->query->has('ikeaConnected')) {
                return $this->render('Shop/PlumScanner/Quote/StepTwo/connectIkea.html.twig', [
                    'designOption' => $designOption,
                    'finishOption' => $finishOption,
                    'colorOption' => $colorOption,
                ]);
            }

            $facadeType = $this->productOptionValueProvider->findFacadeType(Taxon::TAXON_FACADE_METOD);

            /** @var Customer|null $customer */
            $customer = $this->getCustomer();

            $projectDetails = $this->plumScannerApiClient->createProjectViaLink($customer, $projectShareUrlDto->getUrl());

            if (null === $projectDetails) {
                $this->flashBag->add('error', 'app.plum_scanner.create_project_error');

                return $this->redirectToRoute('sylius_shop_homepage');
            }

            $project = $this->projectRepository->findOneBy(['scannerProjectId' => $projectDetails['project_id']]);

            if (null === $project) {
                $project = $this->projectFactory->createForCustomerWithOptionsAndPlumScannerId(
                    $customer,
                    $facadeType,
                    $designOption,
                    $finishOption,
                    $colorOption,
                    [
                        'scannerProjectId' => $projectDetails['project_id']
                    ]
                );

                if (null === $project) {
                    $this->flashBag->add('error', 'app.plum_scanner.create_project_error');

                    return $this->redirectToRoute('sylius_shop_homepage');
                }
            }

            $this->projectRepository->add($project);

            return $this->redirectToRoute('app_plum_scanner_project_status', [
                'token' => $project->getToken(),
            ]);
        }

        return $this->render('Shop/PlumScanner/Quote/StepTwo/sharePlanNewVersion.html.twig', [
            'form' => $form->createView(),
            'designOption' => $designOption,
            'finishOption' => $finishOption,
            'colorOption' => $colorOption,
        ]);
    }

    public function projectStatus(Request $request, Project $project, EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator): Response
    {
        if (null === $project->getScannerProjectId()) { // Project has not been created by scanner
            return $this->redirectToRoute('app_account_project_list');
        }

        $plumScannerResult = $this->plumScannerApiClient->getProjectCompleteStatus($project);
        $projectStatus = $plumScannerResult ? ($plumScannerResult['project_status']??null) : null;

        $failureRedirectUrl = $this->router->generate('app_plum_scanner_share_plan', [
            'designCode' => $project->getDesign()->getCode(),
            'finishCode' => $project->getFinish()->getCode(),
            'colorCode' => $project->getColor()->getCode(),
        ]);

        if (null === $projectStatus) {
            // push analytics data to plum scanner
            $this->plumScannerApiClient->pushAnalyticsData(
                $project->getCustomer(),
                PlumScannerApiClient::ENDPOINT_WEBSITE_EXCEPTION
            );

            $this->flashBag->add('error', 'app.plum_scanner.error_other');

            $event = new ScanStatusEvent($project, $plumScannerResult, ScanStatusEvent::STATUS_KO, $translator->trans('app.plum_scanner.error_other', [], 'flashes'));
            /** @phpstan-ignore-next-line  */
            $eventDispatcher->dispatch($event, ScanStatusEvent::NAME);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['redirectUrl' => $failureRedirectUrl], Response::HTTP_BAD_REQUEST);
            }

            return $this->redirect($failureRedirectUrl);
        }

        // keep previous status in memory because multiple call are possible event if project is already ok
        $previousStatus = $project->getScannerStatus();

        // update project status
        $project->setScannerStatus($projectStatus);
        $this->projectRepository->add($project);

        if (!in_array($projectStatus, PlumScannerApiClient::SUCCESS_SCAN, true)) {
            // push analytics data to plum scanner
            $this->plumScannerApiClient->pushAnalyticsData(
                $project->getCustomer(),
                PlumScannerApiClient::ENDPOINT_ERROR_POPUP
            );

            if (\in_array(
                $projectStatus,
                [PlumScannerApiClient::STATUS_SCAN_MISSING_FRONT, PlumScannerApiClient::STATUS_ERROR_OTHER],
                true
            )) {
                $failureRedirectUrl = $this->router->generate('app_plum_scanner_share_plan', [
                    'designCode' => $project->getDesign()->getCode(),
                    'finishCode' => $project->getFinish()->getCode(),
                    'colorCode' => $project->getColor()->getCode(),
                    'mf' => true,
                ]);
            } else {
                $this->flashBag->add('error', "app.plum_scanner.$projectStatus");
            }

            $event = new ScanStatusEvent($project, $plumScannerResult, ScanStatusEvent::STATUS_KO, $translator->trans("app.plum_scanner.$projectStatus", [], 'flashes'));
            /** @phpstan-ignore-next-line  */
            $eventDispatcher->dispatch($event, ScanStatusEvent::NAME);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['redirectUrl' => $failureRedirectUrl], Response::HTTP_BAD_REQUEST);
            }

            return $this->redirect($failureRedirectUrl);
        }

        $scanCompletedRedirectUrl = $this->router->generate('app_customer_project_show', [
            'token' => $project->getToken()
        ]);

        if ($request->isXmlHttpRequest()) {
            if ($projectStatus === PlumScannerApiClient::STATUS_SCAN_COMPLETED && $previousStatus !== PlumScannerApiClient::STATUS_SCAN_COMPLETED) {
                $event = new ScanStatusEvent($project, $plumScannerResult, ScanStatusEvent::STATUS_OK);
                /** @phpstan-ignore-next-line  */
                $eventDispatcher->dispatch($event, ScanStatusEvent::NAME);
            }

            return new JsonResponse([
                'progress' => $this->plumScannerApiClient->bindProjectProgress($projectStatus),
                'redirectUrl' => $scanCompletedRedirectUrl
            ]);
        }

        if ($projectStatus === PlumScannerApiClient::STATUS_SCAN_COMPLETED) {
            return $this->redirect($scanCompletedRedirectUrl);
        }

        // push analytics data to plum scanner
        $this->plumScannerApiClient->pushAnalyticsData(
            $project->getCustomer(),
            PlumScannerApiClient::ENDPOINT_USER_MAIL_SENT
        );

        return $this->render('Shop/PlumScanner/Quote/StepTwo/projectStatus.html.twig', [
            'project' => $project,
            'scanProgress' => $this->plumScannerApiClient->bindProjectProgress($projectStatus),
        ]);
    }

    public function projectScanTimeout(Request $request, Project $project): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('sylius_shop_homepage');
        }

        // push analytics data to plum scanner when project scan exceed 5 minutes and no email has been sent
        $this->plumScannerApiClient->pushAnalyticsData(
            $project->getCustomer(),
            PlumScannerApiClient::ENDPOINT_WEBSITE_EXCEPTION
        );

        return new JsonResponse([], Response::HTTP_OK);
    }
}
