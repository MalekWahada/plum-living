<?php

declare(strict_types=1);

namespace App\Form\Type\Resources\Page;

use App\Entity\Page\Page;
use App\Form\Type\ColorType;
use App\Form\Type\Resources\PageImageType;
use App\Form\Type\Resources\PageThemeType;
use App\Form\Type\UiElement\ChipCategoryChoiceType;
use App\Form\Type\UiElement\ChipRoomChoiceType;
use App\Form\Type\UiElement\ColorUIType;
use App\Transformer\Locale\LocaleModelTransformer;
use MonsieurBiz\SyliusCmsPagePlugin\Form\Type\PageType;
use Sylius\Bundle\LocaleBundle\Form\Type\LocaleChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfiguredPageType extends PageType
{
    private LocaleModelTransformer $localeModelTransformer;

    public function __construct(LocaleModelTransformer $localeModelTransformer, string $dataClass, array $validationGroups = [])
    {
        $this->localeModelTransformer = $localeModelTransformer;
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('image', PageImageType::class, [
                'label' => 'app.form.page.image'
            ])
            ->add('position', IntegerType::class, [
                'label' => 'app.form.page.position',
            ])
            ->add('referenceLocaleCode', LocaleChoiceType::class, [
                'label' => 'app.form.page.reference_locale_code',
                'required' => true
            ])
        ;

        // Transform locale to code
        $builder
            ->get('referenceLocaleCode')
            ->addModelTransformer($this->localeModelTransformer);

        if ($options['data']->getType() === Page::PAGE_TYPE_MEDIA_ARTICLE) {
            // category
            $builder->add('category', ChipCategoryChoiceType::class, [
                'label'         => 'app.cms_page.media_article.form.category',
            ]);
            // room
            $builder->add('room', ChipRoomChoiceType::class, [
                'required'      => false,
                'label'         => 'app.cms_page.media_article.form.room',
            ]);
            // color
            $builder->add('color', ColorType::class, [
                'required'      => false,
                'label'         => 'app.cms_page.media_article.form.color',
            ]);
            // budget
            $builder->add('budget', NumberType::class, [
                'required'      => false,
                'label'         => 'app.cms_page.media_article.form.budget',
            ]);
            // themes
            $builder->add('themes', CollectionType::class, [
                'by_reference'  => false,
                'entry_type'    => PageThemeType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => 'app.cms_page.media_article.form.theme',
            ]);
        }
    }
}
