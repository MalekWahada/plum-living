<?php

declare(strict_types=1);

namespace App\Form\Type\Resources;

use App\Repository\Taxon\TaxonRepository;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\RichEditorType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CombinationType extends AbstractResourceType
{
    private ProductOptionRepositoryInterface $productOptionRepository;
    private TaxonRepository $taxonRepository;

    public function __construct(
        ProductOptionRepositoryInterface $productOptionRepository,
        TaxonRepository $taxonRepository,
        string $dataClass,
        array $validationGroups = []
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->productOptionRepository = $productOptionRepository;
        $this->taxonRepository = $taxonRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('facadeType', ChoiceType::class, [
            'choices' => $this->taxonRepository->findChoicesFacadeTypes(),
            'choice_label' => function ($choice) {
                return strtoupper($choice->getCode());
            },
            'label' => 'app.form.facade',
        ]);
        $builder->add('design', ProductOptionValueChoiceType::class, [
            'option' => $this->productOptionRepository->findOneBy(['code' => 'design']),
            'required' => false,
            'label' => 'app.form.design',
        ]);
        $builder->add('finish', ProductOptionValueChoiceType::class, [
            'option' => $this->productOptionRepository->findOneBy(['code' => 'finish']),
            'required' => false,
            'label' => 'app.form.finish',
        ]);
        $builder->add('color', ProductOptionValueChoiceType::class, [
            'option' => $this->productOptionRepository->findOneBy(['code' => 'color']),
            'required' => false,
            'label' => 'app.form.color',
        ]);
        $builder->add('recommendation', RichEditorType::class, [
            'required' => false,
            'label' => 'app.form.recommendation.label',
            'tags' => ['recommendation'],
        ]);
        $builder->add('enabled', CheckboxType::class, [
            'required' => false,
            'label' => 'sylius.ui.enabled',
        ]);
        $builder->add('image', CombinationImageType::class, [
            'label' => 'sylius.form.image.file',
            'required' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_combination';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $validationGroups = $this->validationGroups;
                if (null !== $data->getFinish()) {
                    $validationGroups[] = 'design_not_null';
                } elseif (null !== $data->getColor()) {
                    $validationGroups[] = 'finish_not_null';
                }

                if (null !== $data && null === $data->getId()) {
                    $validationGroups[] = 'combination_create';
                }

                return $validationGroups;
            },
        ]);
    }
}
