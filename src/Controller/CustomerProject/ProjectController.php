<?php

declare(strict_types=1);

namespace App\Controller\CustomerProject;

use App\Broker\PlumScannerApiClient;
use App\Broker\PlumScannerApiClientInterface;
use App\Calculator\ProductPriceTaxCalculator;
use App\Controller\CustomerAwareTrait;
use App\Controller\EshopControllerTrait;
use App\Entity\Customer\Customer;
use App\Entity\CustomerProject\Project;
use App\Entity\CustomerProject\ProjectItem;
use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductGroup;
use App\Entity\Product\ProductOption;
use App\Exception\CustomerProject\DuplicatedProjectItemVariantException;
use App\Exception\CustomerProject\UnfetchedProjectException;
use App\Exception\CustomerProject\VariantsInconsistencyWithPlumScannerException;
use App\Factory\CustomerProject\ProjectFactoryInterface;
use App\Factory\CustomerProject\ProjectItemFactoryInterface;
use App\Factory\Order\OrderFactory;
use App\Form\Type\CustomerProject\ProjectType;
use App\Model\CustomerProject\ProjectItemsDetailsPayloadModel;
use App\Model\CustomerProject\ProjectSavePayloadModel;
use App\Processor\CustomerProject\ProjectItemInputProcessor;
use App\Provider\Product\ProductOptionValueProvider;
use App\Repository\CustomerProject\ProjectRepository;
use App\Repository\Product\ProductGroupRepository;
use App\Repository\Product\ProductOptionValueRepository;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectController extends AbstractController
{
    use EshopControllerTrait;
    use CustomerAwareTrait;

    private ProductOptionValueProvider $productOptionValueProvider;
    private OrderFactory $orderFactory;
    private ProjectFactoryInterface $projectFactory;
    private FlashBagInterface $flashBag;
    private PlumScannerApiClientInterface $plumScannerApiClient;
    private ProjectRepository $projectRepository;
    private RepositoryInterface $projectItemRepository;
    private ChannelRepository $channelRepository;
    private OrderRepository $orderRepository;
    private ProductOptionValueRepository $productOptionValueRepository;
    private ProductGroupRepository $productGroupRepository;
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private CurrencyContextInterface $currencyContext;
    private ProductPriceTaxCalculator $productPriceTaxCalculator;
    private ProjectItemFactoryInterface $projectItemFactory;
    private ValidatorInterface $validator;

    private const NOT_FOUND_CODE = 'NOT_FOUND';
    private const SUCCESS_CODE = 'SUCCESS';
    private const FAILED_CODE = 'FAILED';
    private RouterInterface $router;

    public function __construct(
        ProductOptionValueProvider $productOptionValueProvider,
        OrderFactory $orderFactory,
        ProjectFactoryInterface $projectFactory,
        FlashBagInterface $flashBag,
        PlumScannerApiClientInterface $plumScannerApiClient,
        ProjectRepository $projectRepository,
        RepositoryInterface $projectItemRepository,
        ChannelRepository $channelRepository,
        OrderRepository $orderRepository,
        ProductOptionValueRepository $productOptionValueRepository,
        ProductGroupRepository $productGroupRepository,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext,
        CurrencyContextInterface $currencyContext,
        ProductPriceTaxCalculator $productPriceTaxCalculator,
        ProjectItemFactoryInterface $projectItemFactory,
        ValidatorInterface $validator,
        RouterInterface $router
    ) {
        $this->productOptionValueProvider = $productOptionValueProvider;
        $this->orderFactory = $orderFactory;
        $this->projectFactory = $projectFactory;
        $this->flashBag = $flashBag;
        $this->plumScannerApiClient = $plumScannerApiClient;
        $this->projectRepository = $projectRepository;
        $this->projectItemRepository = $projectItemRepository;
        $this->channelRepository = $channelRepository;
        $this->orderRepository = $orderRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->productGroupRepository = $productGroupRepository;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->currencyContext = $currencyContext;
        $this->productPriceTaxCalculator = $productPriceTaxCalculator;
        $this->projectItemFactory = $projectItemFactory;
        $this->validator = $validator;
        $this->router = $router;
    }

    public function projectShow(Request $request, Project $project): Response
    {
        $this->denyAccessUnlessGranted('edit', $project);

        // Scanner has been used if the scannerProjectId is set
        if (null !== $project->getScannerProjectId() && !$project->isScannerFetched()) {
            $errorRedirectResponse = $this->redirectToRoute('app_plum_scanner_share_plan', [
                'designCode' => null !== $project->getDesign() ? $project->getDesign()->getCode() : null,
                'finishCode' => null !== $project->getFinish() ? $project->getFinish()->getCode() : null,
                'colorCode' => null !== $project->getColor() ? $project->getColor()->getCode() : null,
            ]);

            $projectDetails = $this->plumScannerApiClient->getProjectDetails($project);

            if (null === $projectDetails) {
                // push analytics data to plum scanner
                $this->plumScannerApiClient->pushAnalyticsData(
                    $project->getCustomer(),
                    PlumScannerApiClient::ENDPOINT_WEBSITE_EXCEPTION
                );

                $this->flashBag->add('error', 'app.plum_scanner.error_other');

                return $errorRedirectResponse;
            }

            try {
                $project = $this->projectFactory->bindItems($project, $projectDetails['data'] ?? []);
            } catch (DuplicatedProjectItemVariantException|VariantsInconsistencyWithPlumScannerException $exception) {
                // push analytics data to plum scanner
                $this->plumScannerApiClient->pushAnalyticsData(
                    $project->getCustomer(),
                    PlumScannerApiClient::ENDPOINT_WEBSITE_EXCEPTION
                );

                $this->flashBag->add('error', 'app.plum_scanner.error_other');

                $project->setScannerStatus(PlumScannerApiClient::STATUS_ERROR_OTHER);
                $this->projectRepository->add($project);

                return $errorRedirectResponse;
            }

            $this->projectRepository->add($project);
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // flush project changes
            $this->projectRepository->add($project);

            /** @var SubmitButton $addToCartSubmitButton */
            $addToCartSubmitButton = $form->get('addToCart');
            /** @var SubmitButton $saveProjectSubmitButton */
            $saveProjectSubmitButton = $form->get('saveProject');

            if ($addToCartSubmitButton->isClicked()) {
                $order = $this->orderFactory->createForProject(
                    $project,
                    $this->channelContext->getChannel(),
                    $this->localeContext->getLocaleCode(),
                    $this->currencyContext->getCurrencyCode(),
                );

                $this->orderRepository->add($order);

                // push analytics data to plum scanner
                $this->plumScannerApiClient->pushAnalyticsData(
                    $project->getCustomer(),
                    PlumScannerApiClient::ENDPOINT_ADD_TO_CART
                );

                return $this->redirectToRoute('sylius_shop_cart_summary');
            }

            if ($saveProjectSubmitButton->isClicked()) {
                $this->flashBag->add('success', 'app.plum_scanner.project_successfully_saved');
            }

            return $this->redirectToRoute('app_customer_project_show', [
                'token' => $project->getToken()
            ]);
        }

        // push analytics data to plum scanner
        $this->plumScannerApiClient->pushAnalyticsData(
            $project->getCustomer(),
            PlumScannerApiClient::ENDPOINT_PROJECT_LOADED
        );

        return $this->render('Shop/CustomerProject/show.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function bulkProjectItemDetails(Request $request, Project $project): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('sylius_shop_homepage');
        }

        $payload = new ProjectItemsDetailsPayloadModel($request->request->all());
        $validationResult = $payload->validate($this->validator);

        // Validate post
        if ($validationResult->count() > 0) {
            return new JsonResponse(['message' => 'Validation error.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneByCode($payload->getChannelCode());

        /** @var Customer|null $customer */
        $customer = $project->getCustomer();

        // Validate channel
        if (null === $channel || null === $customer) {
            return new JsonResponse(['message' => 'Validation error.'], Response::HTTP_BAD_REQUEST);
        }

        $response = [];
        $notFoundErrorObject = [
            'error' => self::NOT_FOUND_CODE
        ];

        foreach ($payload->getItemsData() as $itemPayload) {
            if (!$itemPayload->isNewItem()) { // Item already exist
                $itemId = $itemPayload->getItemId();

                /** @var ProjectItem|null $projectItem */
                $projectItem = $this->projectItemRepository->find($itemId);

                if (null === $projectItem || $projectItem->getProject() !== $project) {
                    $response[$itemId] = $notFoundErrorObject;
                    continue;
                }
            } else { // New item is added
                $itemId = $itemPayload->getGroupId();
                /** @var ProductGroup|null $group */
                $group = $this->productGroupRepository->find($itemId);

                if (null === $group) {
                    $response[$itemId] = $notFoundErrorObject;
                    continue;
                }

                $projectItem = $this->projectItemFactory->createForProductGroup($group);
            }

            $response[$itemId] = [];
            $processor = new ProjectItemInputProcessor($projectItem, $itemPayload);
            $processor->process($this->productOptionValueRepository, true, false);

            $productVariant = null !== $processor->getMatchedVariant() ? $processor->getMatchedVariant()->getProductVariant() : null;

            if ($processor->hasNoOptionsAvailable()) {
                $response[$itemId]['variants'] = array_map(static function (ProjectItemVariant $variant) {
                    $productVariant = $variant->getProductVariant();
                    $productName = (null !== $productVariant->getProduct()) ? $productVariant->getProduct()->getName() : null;
                    return [
                        'value' => $productVariant->getId(),
                        'text' => $productVariant->getName() ?? $productName
                    ];
                }, $processor->getVariantsAvailable());
            } else {
                $response[$itemId] = array_merge($response[$itemId], array_map(function ($value) {
                    return $this->productOptionValueProvider->reduceOptionsValuesToArray($value);
                }, $processor->getOptionsValues())); // Store options values (design, finish, color) formatted for front
            }

            // Valid item with no price leads to an error
            if ($processor->isVariantMatchedValid() && (null === $productVariant || !$productVariant->hasChannelPricingForChannel($channel))) {
                $response[$itemId] = $notFoundErrorObject;
                continue;
            }

            // Build response
            $response[$itemId] = array_merge($response[$itemId], [
                'options' => $processor->getAvailableOptions(),
                'validOptions' => $processor->getValidOptions(),
                'unitPrice' => null !== $productVariant && $productVariant->hasChannelPricingForChannel($channel) ?
                    $this->productPriceTaxCalculator->calculate($productVariant): null,
            ]);
        }

        return new JsonResponse($response);
    }

    public function projectDetails(Project $project): Response
    {
        $form = $this->createForm(ProjectType::class, $project, ['disabled' => true]);

        return $this->render('Shop/PlumScanner/Project/details.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    public function projectCheckout(Project $project): RedirectResponse
    {
        /** @var Customer|null $customer */
        $customer = $this->getCustomer();

        $order = $this->orderFactory->createForProject(
            $project,
            $this->channelContext->getChannel(),
            $this->localeContext->getLocaleCode(),
            $this->currencyContext->getCurrencyCode(),
            $customer
        );

        $this->orderRepository->add($order);

        // push analytics data to plum scanner
        $this->plumScannerApiClient->pushAnalyticsData(
            $project->getCustomer(),
            PlumScannerApiClient::ENDPOINT_ADD_TO_CART
        );

        return $this->redirectToRoute('sylius_shop_cart_summary');
    }

    public function scheduleCall(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('sylius_shop_homepage');
        }

        /** @var Customer|null $customer */
        $customer = $this->getCustomer();

        if (null === $customer) {
            return new JsonResponse(['message' => 'Invalid customer.'], Response::HTTP_BAD_REQUEST);
        }

        // push analytics data to plum scanner
        $this->plumScannerApiClient->pushAnalyticsData(
            $customer,
            PlumScannerApiClient::ENDPOINT_SCHEDULE_CALL
        );

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function projectAutoSave(Request $request, Project $project) : Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('sylius_shop_homepage');
        }

        /** @var Customer|null $customer */
        $customer = $this->getCustomer();

        $payload = new ProjectSavePayloadModel($request->request->all());
        $validationResult = $payload->validate($this->validator);

        // Validate post
        if (null === $customer || $validationResult->count() > 0) {
            return new JsonResponse(['message' => 'Validation error.'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->request->all();

        // auto-save project name and comment
        if ($payload->getName() !== null && !empty($payload->getName())) {
            $project->setName($payload->getName());
        }
        if ($payload->getComment() !== null && !empty($payload->getComment())) {
            $project->setComment($payload->getComment());
        }

        // auto-save project global options
        if ($payload->getGlobalOptions() !== null && is_array($payload->getGlobalOptions())) {
            foreach ($payload->getGlobalOptions() as $key => $value) {
                $optionValue = $this->productOptionValueRepository->findOneBy(['code' => $value]);

                if (null === $optionValue || !in_array($key, [ProductOption::PRODUCT_OPTION_DESIGN, ProductOption::PRODUCT_OPTION_FINISH, ProductOption::PRODUCT_OPTION_COLOR], true)) {
                    continue;
                }

                $setter = sprintf('set%s', ucfirst($key));
                $project->$setter($optionValue);
            }
        }

        // auto-save project updated items
        $updatedItems = [];
        if ($payload->getUpdatedItems() !== null) {
            foreach ($payload->getUpdatedItems() as $itemPayload) {
                /** @var ProjectItem|null $projectItem */
                $projectItem = $this->projectItemRepository->findOneBy([
                    'id' => (string)$itemPayload->getItemId(),
                    'project' => $project // Item must be a child of current project
                ]);

                if (null === $projectItem) {
                    $updatedItems[$itemPayload->getItemId()] = self::FAILED_CODE;
                    continue;
                }

                $processor = new ProjectItemInputProcessor($projectItem, $itemPayload);
                $processor->process($this->productOptionValueRepository, false, true);
                $variant = $processor->getMatchedVariant();

                // Update comment
                if ($itemPayload->getComment() !== null && !empty($itemPayload->getComment())) {
                    $projectItem->setComment($itemPayload->getComment());
                }

                // Set quantity
                if (null !== $variant) {
                    if ($itemPayload->getQuantity() !== null) {
                        $variant->setQuantity((int)$itemPayload->getQuantity());
                    }

                    $projectItem->setChosenVariant($variant);
                    $updatedItems[$itemPayload->getItemId()] = self::SUCCESS_CODE;
                    continue;
                }

                $updatedItems[$itemPayload->getItemId()] = self::FAILED_CODE;
            }
        }

        // auto-save project removed items
        $removedItems = [];
        if ($payload->getRemovedItems() !== null && is_array($payload->getRemovedItems())) {
            foreach ($data['removedItems'] as $removedItemId) {
                /** @var ProjectItem|null $projectItem */
                $projectItem = $this->projectItemRepository->findOneBy([
                    'id' => (string)$removedItemId,
                    'project' => $project // Item must be a child of current project
                ]);

                if (null === $projectItem || !$project->getItems()->contains($projectItem)) {
                    $removedItems[$removedItemId] = self::FAILED_CODE;
                    continue;
                }

                $project->removeItem($projectItem);
                $removedItems[$removedItemId] = self::SUCCESS_CODE;
            }
        }

        // auto save project new items
        $newItems = [];
        if ($payload->getNewItems() !== null) {
            foreach ($payload->getNewItems() as $itemPayload) {
                /** @var ProductGroup|null $group */
                $group = $this->productGroupRepository->find($itemPayload->getGroupId());

                if (null === $group) {
                    $newItems[$itemPayload->getGroupId()] = self::FAILED_CODE;
                    continue;
                }

                $projectItem = $this->projectItemFactory->createForProductGroup($group);
                $processor = new ProjectItemInputProcessor($projectItem, $itemPayload);
                $processor->process($this->productOptionValueRepository, false, true);
                $variant = $processor->getMatchedVariant();

                if (null === $variant) {
                    $newItems[$itemPayload->getGroupId()] = self::FAILED_CODE;
                    continue;
                }

                // Update comment
                if ($itemPayload->getComment() !== null && !empty($itemPayload->getComment())) {
                    $projectItem->setComment($itemPayload->getComment());
                }

                if ($itemPayload->getQuantity() !== null) {
                    $variant->setQuantity((int)$itemPayload->getQuantity());
                }

                $projectItem->setChosenVariant($variant);

                $project->addItem($projectItem);
                $this->projectRepository->add($project);

                $newItems[$itemPayload->getGroupId()] = $projectItem->getId();
            }
        }

        $this->projectRepository->add($project);

        return new JsonResponse([
            'message' => 'Project successfully auto-saved',
            'updatedItems' => $updatedItems,
            'removedItems' => $removedItems,
            'newItems' => $newItems
        ]);
    }

    public function projectShare(Project $project): RedirectResponse
    {
        /** @var Customer|null $customer */
        $customer = $this->getCustomer();

        if (null === $customer) {
            throw new BadRequestHttpException('Customer is not set');
        }

        if ($customer === $project->getCustomer()) {
            return $this->redirectToRoute('app_customer_project_show', [
                'token' => $project->getToken()
            ]);
        }

        $clone = $this->projectFactory->createForClone($project);
        $clone->setCustomer($customer);
        $clone = $this->projectFactory->createItemsForClonedProject($project, $clone);

        $this->projectRepository->add($clone);

        return $this->redirectToRoute('app_customer_project_show', [
            'token' => $clone->getToken()
        ]);
    }

    public function downloadQuoteFile(Request $request, Project $project): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
        }

        /** @var Customer|null $customer */
        $customer = $this->getCustomer();

        if (null === $customer) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        try {
            $quoteFileLink = $this->plumScannerApiClient->generatePDFQuoteFileLink($project, $channel);

            if (null === $quoteFileLink || !filter_var($quoteFileLink, FILTER_VALIDATE_URL)) {
                return new JsonResponse([], Response::HTTP_BAD_REQUEST);
            }

            $content = file_get_contents($quoteFileLink);

            if (false === $content) {
                return new JsonResponse([], Response::HTTP_BAD_REQUEST);
            }

            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($quoteFileLink) . '"');

            return $response;
        } catch (UnfetchedProjectException $exception) {
            return new JsonResponse([$exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
