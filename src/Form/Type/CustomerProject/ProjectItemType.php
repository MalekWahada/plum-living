<?php

declare(strict_types=1);

namespace App\Form\Type\CustomerProject;

use App\Entity\CustomerProject\ProjectItem;
use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductVariant;
use App\Factory\CustomerProject\ProjectItemFactoryInterface;
use App\Model\CustomerProject\ProjectItemFormModel;
use App\Repository\Product\ProductOptionValueRepository;
use App\Validator\ProductConstraint\ProductVariantExists;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

final class ProjectItemType extends AbstractType
{
    private ProjectItemFactoryInterface $projectItemFactory;

    public function __construct(
        ProjectItemFactoryInterface $projectItemFactory
    ) {
        $this->projectItemFactory = $projectItemFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plumLabel', HiddenType::class)
            ->add('comment', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'app.form.customer_project.item_comment_placeholder',
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'groups' => ['sylius']
                    ])
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'quantity',
                    'min' => 0,
                ],
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'groups' => ['sylius']
                    ])
                ],
                'empty_data' => 1,
            ])
            ->add('validationError', TextType::class, [
                'mapped' => false
            ])

            // Transform ProjectItem entity to a DTO ProjectItemFormModel
            ->addViewTransformer(new CallbackTransformer(
                function ($normToView) {
                    return null !== $normToView ? $this->projectItemFactory->createFormModelFromItem($normToView) : null;
                },
                function ($viewToNorm) {
                    return null !== $viewToNorm ? $this->projectItemFactory->createForFormModel($viewToNorm) : null;
                }
            ))

            // Bellow fields are for item template
            // By default, all options contains all values available. It's useful for returning the form after a validation error
            ->add('groupId', HiddenType::class) // Used for new items
            ->add('addItemChoices', AddProjectItemChoicesType::class, [
                'mapped' => false,
            ])
            ->add('design', EntityType::class, [
                'class' => ProductOptionValue::class,
                'label' => 'app.form.tunnel_shopping.design',
                'attr' => [
                    'class' => 'option ' . ProductOption::PRODUCT_OPTION_DESIGN,
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_OPTION_DESIGN, true); // Allow unique design for new items
                },
            ])
            ->add('finish', EntityType::class, [
                'class' => ProductOptionValue::class,
                'label' => 'app.form.tunnel_shopping.finish',
                'attr' => [
                    'class' => 'option ' . ProductOption::PRODUCT_OPTION_FINISH,
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
            ])
            ->add('color', EntityType::class, [
                'class' => ProductOptionValue::class,
                'label' => 'app.form.tunnel_shopping.color',
                'attr' => [
                    'class' => 'option ' . ProductOption::PRODUCT_OPTION_COLOR,
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_OPTION_COLOR, true); // Allow natural color for new items
                },
            ])
            ->add('handleFinish', EntityType::class, [
                'class' => ProductOptionValue::class,
                'label' => 'app.form.tunnel_shopping.handle_finish',
                'attr' => [
                    'class' => 'option ' . ProductOption::PRODUCT_HANDLE_OPTION_FINISH,
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_HANDLE_OPTION_FINISH);
                },
            ])
            ->add('tapFinish', EntityType::class, [
                'class' => ProductOptionValue::class,
                'label' => 'app.form.tunnel_shopping.tap_finish',
                'attr' => [
                    'class' => 'option ' . ProductOption::PRODUCT_TAP_OPTION_FINISH,
                ],
                'choice_value' => 'code',
                'query_builder' => function (ProductOptionValueRepository $productOptionValueRepository) {
                    return $productOptionValueRepository->findByOptionCodeGetQb(ProductOption::PRODUCT_TAP_OPTION_FINISH);
                },
            ])
            ->add('variant', EntityType::class, [
                'class' => ProjectItemVariant::class,
                'choices' => [],
                'label' => 'app.form.tunnel_shopping.variant',
                'attr' => [
                    'class' => 'option variant',
                ]
            ])
        ;
        // By default, the submitted value must be present in choices for a ChoiceType.
        // As we cannot know choices by advance we need to disable the transformer to allow any values.
        // CAUTION: values will be in string format and saved as productVariantId in model
        $builder->get('variant')->resetViewTransformers();

        /**
         * On project loading for already saved items
         */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (null === $event->getData()) {
                return;
            }

            $this->updateFormWithProjectItemData($event->getForm(), $event->getData());
        });

        /**
         * For new items ONLY on submit, we need to refresh the choices for variant depending on the new project item variants
         * New project items in submit are crated by @see ProjectItemFactory::createForFormModel()
         */
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            if (null === $event->getData() || null !== $event->getData()->getId()) { // Recreate only for new items
                return;
            }

            $this->updateFormWithProjectItemData($event->getForm(), $event->getData());
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectItemFormModel::class,
            'constraints' => [
                new ProductVariantExists([
                    'groups' => ['sylius']
                ])
            ],
            'validation_groups' => ['sylius']
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_project_item';
    }

    /**
     * Replace generic fields with fields containing project item values
     * Add option fields dynamically depending on the item available variants options
     * @param FormInterface $form
     * @param ProjectItem $projectItem
     */
    private function updateFormWithProjectItemData(FormInterface $form, ProjectItem $projectItem): void
    {
        $chosenVariant = $projectItem->getChosenVariant();
        $chosenDesign = $chosenVariant ? $chosenVariant->getDesign() : null;
        $chosenFinish = $chosenVariant ? $chosenVariant->getFinish() : null;
        $chosenColor = $chosenVariant ? $chosenVariant->getColor() : null;
        $chosenHandleFinish = $chosenVariant ? $chosenVariant->getHandleFinish() : null;
        $chosenTapFinish = $chosenVariant ? $chosenVariant->getTapFinish() : null;

        if ($projectItem->hasVariantsWithDesignOption()) {
            $form
                ->remove('design')
                ->add('design', EntityType::class, [
                    'class' => ProductOptionValue::class,
                    'choices' => $projectItem->getAvailableDesigns(),
                    'choice_value' => 'code',
                    'data' => $chosenDesign,
                    'label' => 'app.form.tunnel_shopping.design',
                    'attr' => [
                        'class' => 'option ' . ProductOption::PRODUCT_OPTION_DESIGN,
                    ]
                ]);
        }

        if ($projectItem->hasVariantsWithFinishOption()) {
            $form
                ->remove('finish')
                ->add('finish', EntityType::class, [
                    'class' => ProductOptionValue::class,
                    'choices' => $projectItem->getAvailableFinishes($chosenDesign),
                    'choice_value' => 'code',
                    'data' => $chosenFinish,
                    'label' => 'app.form.tunnel_shopping.finish',
                    'attr' => [
                        'class' => 'option ' . ProductOption::PRODUCT_OPTION_FINISH,
                    ]
                ]);
        }

        if ($projectItem->hasVariantsWithColorOption()) {
            $form
                ->remove('color')
                ->add('color', EntityType::class, [
                    'class' => ProductOptionValue::class,
                    'choices' => $projectItem->getAvailableColors($chosenDesign, $chosenFinish),
                    'choice_value' => 'code',
                    'data' => $chosenColor,
                    'label' => 'app.form.tunnel_shopping.color',
                    'attr' => [
                        'class' => 'option ' . ProductOption::PRODUCT_OPTION_COLOR,
                    ]
                ]);
        }

        if ($projectItem->hasVariantsWithHandleFinishOption()) {
            $form
                ->remove('handleFinish')
                ->add('handleFinish', EntityType::class, [
                    'class' => ProductOptionValue::class,
                    'choices' => $projectItem->getAvailableHandleFinishes(),
                    'choice_value' => 'code',
                    'data' => $chosenHandleFinish,
                    'label' => 'app.form.tunnel_shopping.handle_finish',
                    'attr' => [
                        'class' => 'option ' . ProductOption::PRODUCT_HANDLE_OPTION_FINISH,
                    ]
                ]);
        }

        if ($projectItem->hasVariantsWithTapFinishOption()) {
            $form
                ->remove('tapFinish')
                ->add('tapFinish', EntityType::class, [
                    'class' => ProductOptionValue::class,
                    'choices' => $projectItem->getAvailableTapFinishes(),
                    'choice_value' => 'code',
                    'data' => $chosenTapFinish,
                    'label' => 'app.form.tunnel_shopping.tap_finish',
                    'attr' => [
                        'class' => 'option ' . ProductOption::PRODUCT_TAP_OPTION_FINISH,
                    ]
                ]);
        }

        if (!$projectItem->hasAnyVariantsWithOption()) {
            $form
                ->remove('variant')
                ->add('variant', EntityType::class, [
                    'class' => ProjectItemVariant::class,
                    'choices' => $projectItem->getVariants(),
                    'data' => $chosenVariant,
                    'choice_label' => function (?ProjectItemVariant $itemVariant) {
                        // Item variant can be null if chosenVariant is null
                        return (null !== $itemVariant && null !== $itemVariant->getProductVariant()) ? $itemVariant->getProductVariant()->getName() : $itemVariant->getId();
                    },
                    'choice_value' => function (?ProjectItemVariant $itemVariant) {
                        // Item variant can be null if chosenVariant is null
                        return (null !== $itemVariant && null !== $itemVariant->getProductVariant()) ? $itemVariant->getProductVariant()->getId() : null;
                    },
                    'label' => 'app.form.tunnel_shopping.variant',
                    'attr' => [
                        'class' => 'option variant',
                    ]
                ]);
        }
    }
}
