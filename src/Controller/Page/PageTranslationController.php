<?php

declare(strict_types=1);

namespace App\Controller\Page;

use App\Controller\TranslationController;
use App\Entity\Page\Page;
use App\Translation\PageTranslationTask;
use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusCmsPagePlugin\Repository\PageRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageTranslationController extends TranslationController
{
    private const VALIDATION_GROUPS = ['Default', 'monsieurbiz'];

    private PageRepositoryInterface $pageRepository;

    public function __construct(
        PageTranslationTask $task,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        FlashBagInterface $flashBag,
        RepositoryInterface $localeRepository,
        PageRepositoryInterface $pageRepository
    ) {
        parent::__construct($task, $entityManager, $validator, $translator, $flashBag, $localeRepository);
        $this->pageRepository = $pageRepository;
    }

    public function publishAction(Request $request, Page $page): Response
    {
        $form = $this->getForm(self::PUBLISH_FORM_TOKEN);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processPublishBulk([$page]);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function bulkPublishAction(Request $request): Response
    {
        $form = $this->getForm(self::PUBLISH_FORM_TOKEN);
        $form->submit($request->request->all());

        $ids = $request->request->get('ids');

        if (is_array($ids) && $form->isSubmitted() && $form->isValid()) {
            $objects = $this->pageRepository->findBy(['id' => $ids]);
            $this->processPublishBulk($objects);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function fetchLocaleAction(Request $request, Page $page, string $locale): Response
    {
        $form = $this->getForm(self::FETCH_FORM_TOKEN);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $objects = $this->filterFetchableNonReferenceLocalePages([$page], $locale);
            if ($this->checkFetchablePages($objects)) {
                $this->processFetchBulk($objects, $locale, self::VALIDATION_GROUPS);
            }
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function bulkFetchAction(Request $request, string $locale): Response
    {
        $form = $this->getForm(self::FETCH_FORM_TOKEN);
        $form->submit($request->request->all());

        $ids = $request->request->get('ids');

        if (is_array($ids) && $form->isSubmitted() && $form->isValid()) {
            $objects = $this->filterFetchableNonReferenceLocalePages($this->pageRepository->findBy(['id' => $ids]), $locale);
            if ($this->checkFetchablePages($objects)) {
                $this->processFetchBulk($objects, $locale, self::VALIDATION_GROUPS);
            }
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * @param array|Page[] $pages
     * @param string $locale
     * @return array
     */
    private function filterFetchableNonReferenceLocalePages(array $pages, string $locale): array
    {
        return array_filter($pages, static function (Page $page) use ($locale) {
            return $page->getReferenceLocaleCode() !== $locale; // Prevent fetching the reference locale
        });
    }

    /**
     * @param array|Page[] $objects
     * @return bool
     */
    private function checkFetchablePages(array $objects): bool
    {
        foreach ($objects as $object) {
            // Ref locale must be published again before fetching translations
            $currentHash = $object->generateContentHash($object->getReferenceLocaleCode());
            if ((null !== $hash = $object->getTranslationsPublishedContentHash()) && $hash !== $currentHash) {
                $this->flashBag->add('error', $this->translator->trans(
                    'app.ui.admin.translation.translation_reference_content_has_changed',
                    [
                        '%locale%' => Locales::getName($object->getReferenceLocaleCode()),
                        '%code%' => $object->getCode()
                    ]
                ));
                return false;
            }
        }
        return true;
    }
}
