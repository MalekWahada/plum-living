<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Controller\TranslationController;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCompleteInfo;
use App\Entity\Product\ProductCompleteInfoTranslation;
use App\Translation\ProductCompleteInfoTranslationTask;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductCompleteInfoTranslationController extends TranslationController
{
    private const VALIDATION_GROUPS = ['Default', 'sylius'];

    private ProductRepositoryInterface $productRepository;

    public function __construct(
        ProductCompleteInfoTranslationTask $task,
        EntityManagerInterface $entityManager,
        FlashBagInterface      $flashBag,
        TranslatorInterface    $translator,
        RepositoryInterface    $localeRepository,
        ProductRepositoryInterface $productRepository,
        ValidatorInterface     $validator
    ) {
        parent::__construct($task, $entityManager, $validator, $translator, $flashBag, $localeRepository);
        $this->productRepository = $productRepository;
    }

    public function publishAction(Request $request, Product $product): Response
    {
        $form = $this->getForm(self::PUBLISH_FORM_TOKEN);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $objects = $this->getProductsCompleteInfo([$product]);
            $this->processPublishBulk($objects);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function bulkPublishAction(Request $request): Response
    {
        $form = $this->getForm(self::PUBLISH_FORM_TOKEN);
        $form->submit($request->request->all());

        $ids = $request->request->get('ids');

        if (is_array($ids) && $form->isSubmitted() && $form->isValid()) {
            $objects = $this->getProductsCompleteInfo($this->productRepository->findBy(['id' => $ids]));
            $this->processPublishBulk($objects);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function fetchLocaleAction(Request $request, Product $product, string $locale): Response
    {
        if ($locale === ProductCompleteInfoTranslation::PUBLISHED_LOCALE) { // Cannot fetch default locale
            return new RedirectResponse($request->headers->get('referer'));
        }

        $form = $this->getForm(self::FETCH_FORM_TOKEN);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $objects = $this->getProductsCompleteInfo([$product]);
            if ($this->checkFetchableProductsCompleteInfo($objects)) {
                $this->processFetchBulk($objects, $locale, self::VALIDATION_GROUPS);
            }
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function bulkFetchAction(Request $request, string $locale): Response
    {
        if ($locale === ProductCompleteInfoTranslation::PUBLISHED_LOCALE) { // Cannot fetch default locale
            return new RedirectResponse($request->headers->get('referer'));
        }

        $form = $this->getForm(self::FETCH_FORM_TOKEN);
        $form->submit($request->request->all());

        $ids = $request->request->get('ids');

        if (is_array($ids) && $form->isSubmitted() && $form->isValid()) {
            $objects = $this->getProductsCompleteInfo($this->productRepository->findBy(['id' => $ids]));
            if ($this->checkFetchableProductsCompleteInfo($objects)) {
                $this->processFetchBulk($objects, $locale, self::VALIDATION_GROUPS);
            }
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * @param array|Product[] $products
     * @return array
     */
    private function getProductsCompleteInfo(array $products): array
    {
        return array_map(static function (Product $product) {
            return $product->getCompleteInfo();
        }, array_filter($products, static function (Product $product) {
            return null !== $product->getCompleteInfo() && $product->getCompleteInfo()->isEnabled();
        }));
    }

    /**
     * @param array|ProductCompleteInfo[] $objects
     * @return bool
     */
    private function checkFetchableProductsCompleteInfo(array $objects): bool
    {
        foreach ($objects as $object) {
            // Ref locale must be published again before fetching translations
            $currentHash = $object->generateContentHash(ProductCompleteInfoTranslation::PUBLISHED_LOCALE);
            if ((null !== $hash = $object->getTranslationsPublishedContentHash()) && $hash !== $currentHash) {
                $this->flashBag->add('error', $this->translator->trans(
                    'app.ui.admin.translation.translation_reference_content_has_changed',
                    [
                        '%locale%' => Locales::getName(ProductCompleteInfoTranslation::PUBLISHED_LOCALE)
                    ]
                ));
                return false;
            }
        }
        return true;
    }
}
