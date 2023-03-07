<?php

declare(strict_types=1);

namespace App\Form\Type\CustomerProject;

use App\Dto\CustomerProject\ProjectShareUrlDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('url', UrlType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'app.ui.plum_scanner.step_two.share_plan_new.link_placeholder',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectShareUrlDto::class,
        ]);
    }
}
