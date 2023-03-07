<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Entity\Order\Order;
use App\Provider\User\ShopUserProvider;
use Exception;
use Payum\Core\Payum;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRouteInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

final class PayumController
{
    private Payum $payum;
    private OrderRepositoryInterface $orderRepository;
    private RouterInterface $router;
    private GetStatusFactoryInterface $getStatusRequestFactory;
    private ResolveNextRouteFactoryInterface $resolveNextRouteRequestFactory;
    private Environment $twig;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private ShopUserProvider $shopUserProvider;
    private FlashBagInterface $flashBagHandler;

    public function __construct(
        Payum $payum,
        OrderRepositoryInterface $orderRepository,
        RouterInterface $router,
        GetStatusFactoryInterface $getStatusFactory,
        ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        Environment $twig,
        CsrfTokenManagerInterface $csrfTokenManager,
        ShopUserProvider $shopUserProvider,
        FlashBagInterface $flashBagHandler
    ) {
        $this->payum = $payum;
        $this->orderRepository = $orderRepository;
        $this->router = $router;
        $this->getStatusRequestFactory = $getStatusFactory;
        $this->resolveNextRouteRequestFactory = $resolveNextRouteFactory;
        $this->twig = $twig;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->shopUserProvider = $shopUserProvider;
        $this->flashBagHandler = $flashBagHandler;
    }

    /**
     * @throws Exception
     */
    public function afterCaptureAction(Request $request): Exception
    {
        throw new Exception('Payum Controller');

        /*
         $token = $this->getHttpRequestVerifier()->verify($request);

         $status = $this->getStatusRequestFactory->createNewWithModel($token);
         $this->payum->getGateway($token->getGatewayName())->execute($status);

         $resolveNextRoute = $this->resolveNextRouteRequestFactory->createNewWithModel($status->getFirstModel());
         $this->payum->getGateway($token->getGatewayName())->execute($resolveNextRoute);

         $this->getHttpRequestVerifier()->invalidate($token);

         $routeUrl = $this->resolveNotPaidOrder($resolveNextRoute);

         if (PaymentInterface::STATE_NEW !== $status->getValue()) {
             $flashBag = $request->getSession()->getBag('flashes');
             $flashBag->add('info', sprintf('sylius.payment.%s', $status->getValue()));
         }

         return new RedirectResponse($routeUrl); */
    }

    public function confirmNotPaidOrder(string $tokenValue): Response
    {
        $order = $this->getOrder($tokenValue);

        if (null === $order) {
            $this->flashBagHandler->add('warning', 'app.order.awaiting_payment.not_found');
            return $this->redirectToHomePage();
        }

        if ($order->getPaymentState() === OrderPaymentStates::STATE_CANCELLED || !$this->isCustomerOrder($order)) {
            $this->flashBagHandler->add('warning', 'app.order.awaiting_payment.not_allowed');
            return $this->redirectToHomePage();
        }

        return new Response($this->twig->render('Shop/Payum/not_paid_order_confirmation.html.twig', [
            '_csrf_token' => $this->csrfTokenManager->getToken((string)$order->getId())->getValue(),
            'order' => $order,
        ]));
    }

    public function redirectAfterOrderToCart(): Response
    {
        $this->flashBagHandler->clear();
        $this->flashBagHandler->add('success', 'app.order.awaiting_payment.to_cart_success');

        return $this->redirectToHomePage();
    }

    private function resolveNotPaidOrder(ResolveNextRouteInterface $resolveNextRoute): string
    {
        $resolveNextRouteParameters = $resolveNextRoute->getRouteParameters();
        if (array_key_exists('tokenValue', $resolveNextRouteParameters)) {
            $tokenValue = $resolveNextRouteParameters['tokenValue'];
            $order = $this->getOrder($tokenValue);

            if (null !== $order && $order->getPaymentState() === OrderPaymentStates::STATE_AWAITING_PAYMENT) {
                return $this->generateRouteUrlWithParams('app_confirm_not_paid_order', [
                    'tokenValue' => $tokenValue,
                ]);
            }
        }

        return $this->generateRouteUrlWithParams($resolveNextRoute->getRouteName(), $resolveNextRouteParameters);
    }

    private function getHttpRequestVerifier(): HttpRequestVerifierInterface
    {
        return $this->payum->getHttpRequestVerifier();
    }

    private function getOrder(string $tokenValue): ?Order
    {
        return $this->orderRepository->findOneBy([
            'tokenValue' => $tokenValue,
        ]);
    }

    private function isCustomerOrder(Order $order): bool
    {
        $currentShopUser = $this->shopUserProvider->getShopUser();
        $orderCustomer = $order->getCustomer();

        if ($currentShopUser !== null && $currentShopUser->getCustomer() === $orderCustomer) {
            return true;
        }

        return false;
    }

    private function redirectToHomePage(): Response
    {
        return new RedirectResponse($this->generateRouteUrlWithParams('sylius_shop_homepage'));
    }

    private function generateRouteUrlWithParams(string $routeName, array $routePrams = []): string
    {
        return $this->router->generate($routeName, $routePrams);
    }
}
