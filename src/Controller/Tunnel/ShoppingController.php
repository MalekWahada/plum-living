<?php

declare(strict_types=1);

namespace App\Controller\Tunnel;

use App\Controller\EshopControllerTrait;
use App\Entity\Product\Product;
use App\Entity\Taxonomy\Taxon;
use App\Provider\Image\Product\ProductVariantImageProvider;
use App\Provider\Product\ProductOptionValueProvider;
use App\Provider\Product\ProductProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ShoppingController extends AbstractController
{
    use EshopControllerTrait;

    private ProductOptionValueProvider $productOptionValueProvider;
    private ProductProvider $productProvider;
    private FlashBagInterface $flashBag;
    private ProductVariantImageProvider $variantImageProvider;

    public function __construct(
        ProductOptionValueProvider $productOptionValueProvider,
        ProductProvider $productProvider,
        FlashBagInterface $flashBag,
        ProductVariantImageProvider $variantImageProvider
    ) {
        $this->productOptionValueProvider = $productOptionValueProvider;
        $this->productProvider = $productProvider;
        $this->flashBag = $flashBag;
        $this->variantImageProvider = $variantImageProvider;
    }

    public function getFacadeOption(
        Request $request,
        string $facadeTypeCode,
        ?string $designCode,
        ?string $finishCode,
        ?string $colorCode
    ): Response {
        $options = $this->productOptionValueProvider->getByVariantsSuccession(
            $facadeTypeCode,
            $designCode,
            $finishCode,
            $colorCode
        );

        if (null === $finishCode && isset($options['finishes']) && 1 === \count($options['finishes'])) {
            return $this->redirectToRoute('facade_get_colors', [
                'facadeTypeCode' => $facadeTypeCode,
                'designCode' => $designCode,
                'finishCode' => current($options['finishes'])->getCode(),
            ]);
        }

        if (null === $options) {
            return $this->facadeDoesNotExistError();
        }

        return $this->returnEshopViews(
            $options,
            'Shop/Tunnel/Shopping/index.html.twig',
            $request->attributes->get('_template'),
            $request->isXmlHttpRequest()
        );
    }

    public function getProducts(
        Request $request,
        string $facadeTypeCode,
        string $designCode,
        string $finishCode,
        string $colorCode
    ): Response {
        $template = $request->attributes->get('_template');
        $options = $this->productOptionValueProvider->getByVariantsSuccession(
            $facadeTypeCode,
            $designCode,
            $finishCode,
            $colorCode
        );

        $options['products'] = $this->productProvider->getProductPerTaxon(
            $options['facadeType'],
            $options['design'],
            $options['finish'],
            $options['color']
        );
        $options['productTaxons'] = array_combine( // Create taxon code => name array
            array_keys($options['products']),
            array_column($options['products'], 'taxonName'),
        );

        $options['sideBarMenu'] = $this->renderView(
            "Shop/Tunnel/Shopping/Partial/_sidebar.html.twig",
            [
                'facadeType' => $options['facadeType'],
                'design' => $options['design'] ?? null,
                'finish' => $options['finish'] ?? null,
                'color' => $options['color'] ?? null,
                'productTaxons' => $options['productTaxons']
            ]
        );

        $options['optionListView'] = $this->renderView($template, $options);

        if ($request->isXmlHttpRequest()) {
            $categoriesTemplate = $this->renderView(
                "Shop/Tunnel/Shopping/Partial/_product_categories_items.html.twig",
                [
                    'productTaxons' => $options['productTaxons']
                ]
            );
            $response = [
                'mainView' => $options['optionListView'],
                'sideBarMenu' => $options['sideBarMenu'],
                'secondaryView' => $categoriesTemplate
            ];

            return new JsonResponse($response);
        }

        return $this->render('Shop/Tunnel/Shopping/index.html.twig', $options);
    }

    public function getProductVariantImage(string $productCode, string $colorCode): JsonResponse
    {
        $imagePaths = $this->variantImageProvider->getImagePathsByProductCodeAndColorCode($productCode, $colorCode);

        return new JsonResponse($imagePaths);
    }

    public function getTunnelProduct(Request $request, string $slug): Response
    {
        /** @var Product|null $product */
        $product = $this->productProvider->getProductBySlug($slug);
        if (null === $product) {
            $this->flashBag->add('warning', 'app.product.not_found');
            return $this->redirectToRoute('sylius_shop_homepage');
        }

        $selectedOptionsValues = $product->hasFacadeOptions() ?
            $this->productOptionValueProvider->setOptionValuesCodesFromProduct(
                $product,
                $request->query->all()
            ) : ProductOptionValueProvider::EMPTY_OPTION_KEYS;

        return $this->render('Shop/Tunnel/Shopping/Product/show_complete_infos.html.twig', [
            'product' => $product,
            'selectedOptionsValues' => $selectedOptionsValues,
        ]);
    }

    private function facadeDoesNotExistError() : Response
    {
        $this->flashBag->add('error', 'app.facade.facade_not_exist');

        return $this->redirectToRoute('sylius_shop_homepage');
    }
}
