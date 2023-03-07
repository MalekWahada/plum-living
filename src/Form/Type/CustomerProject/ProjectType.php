<?php

declare(strict_types=1);

namespace App\Form\Type\CustomerProject;

use App\Entity\CustomerProject\Project;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Repository\Product\ProductOptionValueRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class ProjectType extends AbstractType
{
    private ProductOptionValueRepository $productOptionValueRepository;

    public function __construct(ProductOptionValueRepository $productOptionValueRepository)
    {
        $this->productOptionValueRepository = $productOptionValueRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'groups' => ['sylius']
                    ])
                ]
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'app.form.customer_project.comment_placeholder',
                    'maxlength' => 255,
                    'class' => 'ps-project-item__comment t-label-small',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'groups' => ['sylius']
                    ])
                ]
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => ProjectItemType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('design', EntityType::class, [
                'label' => 'app.form.tunnel_shopping.design',
                'class' => ProductOptionValue::class,
                'placeholder' => null,
                'attr' => [
                    'class' => 'option design',
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_OPTION_DESIGN);
                },
                'required' => false
            ])
            ->add('finish', EntityType::class, [
                'label' => 'app.form.tunnel_shopping.finish',
                'class' => ProductOptionValue::class,
                'placeholder' => null,
                'attr' => [
                    'class' => 'option finish',
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    $finishes = [
                        ProductOptionValue::FINISH_LACQUER_MATT_CODE,
                        ProductOptionValue::FINISH_OAK_PAINTED_CODE,
                        ProductOptionValue::FINISH_OAK_NATURAL_CODE,
                        ProductOptionValue::FINISH_WALNUT_NATURAL_CODE,
                    ];
                    return $productOptionValueRepository->findByOptionAndCodesGetQb(ProductOption::PRODUCT_OPTION_FINISH, $finishes);
                },
                'required' => false
            ])
            ->add('color', EntityType::class, [
                'label' => 'app.form.tunnel_shopping.color',
                'class' => ProductOptionValue::class,
                'placeholder' => null,
                'attr' => [
                    'class' => 'option color',
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_OPTION_COLOR);
                },
                'required' => false
            ])
            ->add('addToCart', SubmitType::class, [
                'label' => 'sylius.ui.add_to_cart',
            ])
            ->add('saveProject', SubmitType::class, [
                'label' => 'app.ui.plum_scanner.step_three.actions.save_project',
            ])
            ->add('downloadQuoteFile', ButtonType::class, [
                'label' => 'app.ui.plum_scanner.step_three.actions.download_quote_file',
            ])
        ;

        // bypass hidden natural color validation if set for project
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            if (null === ($data = $event->getData())) {
                return;
            }
            $form = $event->getForm();

            $naturalColor = $this->productOptionValueRepository->findOneByCodeAndOptionCode(
                ProductOptionValue::COLOR_NATURAL_CODE,
                ProductOption::PRODUCT_OPTION_COLOR
            );

            if (null === $naturalColor) {
                return;
            }

            if ((int)$data['color'] === $naturalColor->getId()) {
                $form->remove('color');
                $form->add('color', EntityType::class, [
                    'label' => 'app.form.tunnel_shopping.color',
                    'class' => ProductOptionValue::class,
                    'placeholder' => null,
                    'attr' => [
                        'class' => 'option color',
                    ],
                    'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                        return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_OPTION_COLOR, true);
                    },
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'validation_groups' => ['sylius'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_project';
    }
}
