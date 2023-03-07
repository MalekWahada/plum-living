<?php

declare(strict_types=1);

namespace App\Controller\Cart;

use App\Dto\Cart\ShareCart;
use App\EmailManager\Cart\ShareCartEmailManager;
use App\Entity\Order\Order;
use App\Form\Type\Cart\ShareCartType;
use App\Order\Generator\OrderCacheGeneratorInterface;
use App\Order\Generator\CartItemsGenerator;
use App\Model\CachedOrder\CachedOrderModel;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class CartController
{
    private Environment $twig;
    private FlashBagInterface $flashBag;
    private FormFactoryInterface $formFactory;
    private ShareCartEmailManager $emailManager;
    private CartItemsGenerator $cartItemsGenerator;
    private RouterInterface $router;
    private OrderRepositoryInterface $orderRepository;
    private OrderCacheGeneratorInterface $orderCacheGenerator;

    public function __construct(
        Environment                  $twig,
        FlashBagInterface            $flashBag,
        FormFactoryInterface         $formFactory,
        ShareCartEmailManager        $emailManager,
        CartItemsGenerator           $cartItemsGenerator,
        RouterInterface              $router,
        OrderRepositoryInterface     $orderRepository,
        OrderCacheGeneratorInterface $orderCacheGenerator
    ) {
        $this->twig = $twig;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->emailManager = $emailManager;
        $this->cartItemsGenerator = $cartItemsGenerator;
        $this->router = $router;
        $this->orderRepository = $orderRepository;
        $this->orderCacheGenerator = $orderCacheGenerator;
    }

    public function shareCart(Request $request, string $tokenValue): Response
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $tokenValue]);
        if ($order === null) {
            return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
        }

        $shareCartDTO = new ShareCart();
        $form = $this->formFactory->createBuilder(ShareCartType::class, $shareCartDTO)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $orderCacheToken = $this->orderCacheGenerator->generate($order);
            $this->emailManager->shareCart($shareCartDTO->getEmail(), $orderCacheToken);
            $this->flashBag->add('success', 'app.share_cart.success');

            return new RedirectResponse($request->headers->get('referer'));
        }
        $content = $this->twig->render('Shop/Cart/Share/ShareModal.html.twig', [
            'form' => $form->createView(),
            'order' => $order,
        ]);

        return new Response($content);
    }

    public function duplicateCart(string $orderCacheToken): Response
    {
        /** @var CachedOrderModel|null $cachedOrder */
        $cachedOrder = $this->orderCacheGenerator->getCachedOrder($orderCacheToken);
        $url = $this->router->generate('sylius_shop_cart_summary');

        if ($cachedOrder !== null) {
            $this->cartItemsGenerator->generateViaCachedOrder($cachedOrder);
            $this->flashBag->add('success', 'app.duplicate_cart.success');
        } else {
            $this->flashBag->add('error', 'app.duplicate_cart.error');
            $url = $this->router->generate('sylius_shop_homepage');
        }

        return new RedirectResponse($url);
    }
}
