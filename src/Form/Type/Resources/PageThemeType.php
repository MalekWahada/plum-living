<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use App\Entity\Page\PageTheme;
use App\Provider\CMS\Chip\ChipProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageThemeType extends AbstractType
{
    private ChipProvider $chipProvider;

    public function __construct(ChipProvider $chipProvider)
    {
        $this->chipProvider = $chipProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $themes = $this->chipProvider->getThemes();

        $builder
            ->add('theme', ChoiceType::class, [
                'choices'      => $themes,
                'required'     => false,
                'multiple'     => false,
                'choice_label' => fn (int $value, string $slug): string => 'app.cms_page.media_article.theme.' . $slug . '.title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageTheme::class,
        ]);
    }
}
