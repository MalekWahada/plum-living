<?php

declare(strict_types=1);

namespace App\Controller\Taxon;

use App\Controller\TranslationController;
use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonTranslation;
use App\Translation\TaxonTranslationTask;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaxonTranslationController extends TranslationController
{
    private const VALIDATION_GROUPS = ['Default', 'sylius'];

    public function __construct(
        TaxonTranslationTask $task,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        FlashBagInterface $flashBag,
        RepositoryInterface $localeRepository
    ) {
        parent::__construct($task, $entityManager, $validator, $translator, $flashBag, $localeRepository);
    }

    public function publishAction(Request $request, Taxon $taxon): Response
    {
        $form = $this->getForm(self::PUBLISH_FORM_TOKEN);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processPublishBulk([$taxon]);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    public function fetchLocaleAction(Request $request, Taxon $taxon, string $locale): Response
    {
        if ($locale === TaxonTranslation::PUBLISHED_LOCALE) { // Cannot fetch default locale
            return new RedirectResponse($request->headers->get('referer'));
        }

        $form = $this->getForm(self::FETCH_FORM_TOKEN);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid() && $this->checkFetchableTaxons([$taxon])) {
            $this->processFetchBulk([$taxon], $locale, self::VALIDATION_GROUPS);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * @param array|Taxon[] $objects
     * @return bool
     */
    private function checkFetchableTaxons(array $objects): bool
    {
        foreach ($objects as $object) {
            // Ref locale must be published again before fetching translations
            $currentHash = $object->generateContentHash(TaxonTranslation::PUBLISHED_LOCALE);
            if ((null !== $hash = $object->getTranslationsPublishedContentHash()) && $hash !== $currentHash) {
                $this->flashBag->add('error', $this->translator->trans(
                    'app.ui.admin.translation.translation_reference_content_has_changed',
                    [
                        '%locale%' => Locales::getName(TaxonTranslation::PUBLISHED_LOCALE),
                        '%code%' => $object->getCode()
                    ]
                ));
                return false;
            }
        }
        return true;
    }
}
