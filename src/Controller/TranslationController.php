<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page\Page;
use App\Entity\Product\ProductCompleteInfo;
use App\Entity\Product\ProductCompleteInfoTranslation;
use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonTranslation;
use App\Entity\Translation\ExternallyTranslatableInterface;
use App\Model\Translation\TranslationTaskResult;
use App\Translation\TranslationTaskInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class TranslationController extends AbstractController
{
    public const PUBLISH_FORM_TOKEN = 'app_translation_publish';
    public const FETCH_FORM_TOKEN = 'app_translation_fetch';

    protected TranslationTaskInterface $task;
    protected TranslatorInterface $translator;
    protected FlashBagInterface $flashBag;
    private RepositoryInterface $localeRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(TranslationTaskInterface $task, EntityManagerInterface $entityManager, ValidatorInterface $validator, TranslatorInterface $translator, FlashBagInterface $flashBag, RepositoryInterface $localeRepository)
    {
        $this->task = $task;
        $this->translator = $translator;
        $this->flashBag = $flashBag;
        $this->localeRepository = $localeRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    protected function getForm(string $token): FormInterface
    {
        return $this->createFormBuilder(null, [
            'csrf_token_id' => $token,
            'csrf_field_name' => '_csrf_token',
            'allow_extra_fields' => true
        ])->getForm();
    }

    /**
     * @param array|ResourceInterface[] $objects
     */
    protected function processPublishBulk(array $objects): void
    {
        if (empty($objects)) {
            $this->flashBag->add('warning', $this->translator->trans('app.ui.admin.translation.nothing_to_process'));
            return;
        }

        try {
            $result = $this->task->bulkPublish($objects);

            if (!$result->hasErrors()) {
                foreach ($objects as $object) {
                    if ($object instanceof ExternallyTranslatableInterface) {
                        $object->setTranslationsPublishedAt(new \DateTime());

                        if ($object instanceof Page) { // Only page hase a reference locale code
                            $object->setTranslationsPublishedContentHash($object->generateContentHash($object->getReferenceLocaleCode()));
                        } else {
                            $object->setTranslationsPublishedContentHash($object->generateContentHash());
                        }
                    }
                    $this->entityManager->persist($object);
                }
                $this->entityManager->flush();
                $this->flashBag->add('success', $this->translator->trans('app.ui.admin.translation.publish_translation_result', [
                    '%nb%' => $result->getSucceeded(),
                ]));
            } else {
                $this->flashBag->add('error', $this->translator->trans('app.ui.admin.translation.publish_translation_error'));
                $this->processResultErrors($result);
            }
        } catch (\Exception $e) {
            $this->flashBag->add('error', $this->translator->trans('app.ui.admin.translation.translation_task_error', [
                '%error%' => $e->getMessage()
            ]));
        }
    }

    protected function processFetchBulk(array $objects, string $locale, array $validationGroups): void
    {
        $this->checkValidLocale($locale);

        if (empty($objects)) {
            $this->flashBag->add('warning', $this->translator->trans('app.ui.admin.translation.nothing_to_process'));
            return;
        }

        try {
            $result = $this->task->bulkFetch($objects, $locale);
            $validationResult = $this->validator->validate($objects, null, $validationGroups); // Validate page

            if (!$result->hasErrors() && 0 === $validationResult->count()) {
                foreach ($objects as $page) {
                    $this->entityManager->persist($page);
                }
                $this->entityManager->flush();
                $this->flashBag->add('success', $this->translator->trans('app.ui.admin.translation.fetch_translation_result', [
                    '%nb%' => $result->getSucceeded(),
                    '%locale%' => Locales::getName($locale)
                ]));
            } else {
                $this->flashBag->add('error', $this->translator->trans('app.ui.admin.translation.fetch_translation_error', [
                    '%locale%' => Locales::getName($locale),
                ]));
                $this->processResultErrors($result);
                $this->processValidationErrors($validationResult);
            }
        } catch (\Exception $e) {
            $this->flashBag->add('error', $this->translator->trans('app.ui.admin.translation.translation_task_error', [
                '%error%' => $e->getMessage()
            ]));
        }
    }

    private function checkValidLocale(string $locale): void
    {
        if (null === $this->localeRepository->findOneBy(['code' => $locale])) {
            throw new \InvalidArgumentException('Locale not found');
        }
    }

    private function processResultErrors(TranslationTaskResult $result): void
    {
        foreach ($result->getErrors() as $error) {
            $this->flashBag->add('error', $this->translator->trans(
                $error->getMessage(),
                [
                    '%details%' => $error->getDetails(),
                    '%locale%' => (null !== $error->getLocale()) ? Locales::getName($error->getLocale()) : null
                ],
                'validators'
            ));
        }
    }

    private function processValidationErrors(ConstraintViolationListInterface $validationResult): void
    {
        foreach ($validationResult as $error) {
            $this->flashBag->add('error', $this->translator->trans(
                'app.translation.validation_failed_on_field',
                [
                    '%field%' => $error->getPropertyPath(),
                    '%error%' => $error->getMessage()
                ],
                'validators'
            ));
        }
    }
}
