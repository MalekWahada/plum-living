<?php

declare(strict_types=1);

namespace App\Form\Extension\Page;

use App\Entity\Locale\Locale;
use App\Entity\Page\Page;
use App\Form\Type\Resources\PageImageType;
use MonsieurBiz\SyliusCmsPagePlugin\Form\Type\PageType;
use Sylius\Bundle\LocaleBundle\Form\Type\LocaleChoiceType;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use function in_array;

final class PageTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var Page|null $data */
            $data = $event->getData();
            if (null !== $data && in_array($data->getType(), Page::NOT_SIMPLE_PAGE_TYPES, true)) {
                $event->getForm()->add('position', IntegerType::class, [
                    'label' => 'app.form.page.position',
                ]);

                return;
            }

            $form = $event->getForm();
            $form->add('type', ChoiceType::class, [
                'label' => 'app.form.page.type',
                'choices' => Page::getAllowedSimplePageTypes(),
                'choice_label' => fn (string $value): string => 'app.ui.page.type_' . $value,
            ]);
        });

        $builder
            ->add('image', PageImageType::class, [
                'label' => 'app.form.page.image'
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            PageType::class,
        ];
    }
}
