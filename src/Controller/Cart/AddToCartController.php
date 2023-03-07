<?php

declare(strict_types=1);

namespace App\Controller\Cart;

use App\Formatter\ProductVariant\StringToProductVariantModelArrayFormatter;
use App\Order\Generator\CartItemsGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class AddToCartController
{
    private StringToProductVariantModelArrayFormatter $toProductVariantModelArrayFormatter;
    private RouterInterface $router;
    private CartItemsGenerator $cartItemsGenerator;
    private FlashBagInterface $flashBag;

    public function __construct(
        StringToProductVariantModelArrayFormatter $toProductVariantModelArrayFormatter,
        RouterInterface                           $router,
        CartItemsGenerator                        $cartItemsGenerator,
        FlashBagInterface                         $flashBag
    ) {
        $this->toProductVariantModelArrayFormatter = $toProductVariantModelArrayFormatter;
        $this->router = $router;
        $this->cartItemsGenerator = $cartItemsGenerator;
        $this->flashBag = $flashBag;
    }

    // e.g.: non-encoded   /add-to-cart/6040-01S-L00|2;6040-01S-L02|2
    //           encoded   /add-to-cart/6040-01S-L00%7C2;6040-01S-L02%7C2
    public function addToCartViaLink(Request $request): Response
    {
        $this->flashBag->clear();

        $variantCodesQuantitiesStr = $request->get('variants_codes_quantities');

        $productVariantModels = $this->toProductVariantModelArrayFormatter->format($variantCodesQuantitiesStr);

        if (null === $productVariantModels) {
            $this->flashBag->add('warning', 'app.cart.error');
        } else {
            $this->cartItemsGenerator->generateViaProductVariantModels($productVariantModels);
            $this->flashBag->add('success', 'app.cart.success');
        }

        if ($request->query->has('directCheckout') && $request->query->getBoolean('directCheckout')) {
            return new RedirectResponse($this->router->generate('sylius_shop_cart_summary'));
        }
        if ($request->query->has('url')) {
            return new RedirectResponse($request->query->get('url'));
        }
        if ($request->query->has('json')) {
            return new JsonResponse([
                'success' => ! is_null($productVariantModels),
            ]);
        }
        return new RedirectResponse($this->router->generate('sylius_shop_homepage'));
    }
}
