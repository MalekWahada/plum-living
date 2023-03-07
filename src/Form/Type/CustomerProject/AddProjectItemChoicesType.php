<?php

declare(strict_types=1);

namespace App\Form\Type\CustomerProject;

use App\Entity\CustomerProject\ProjectItem;
use App\Entity\Product\ProductGroup;
use App\Entity\Product\ProductOption;
use App\Entity\Taxonomy\Taxon;
use App\Model\Product\ProductGroupChoiceModel;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AddProjectItemChoicesType extends AbstractType
{
    private TaxonRepositoryInterface $taxonRepository;

    public function __construct(TaxonRepositoryInterface $taxonRepository)
    {
        $this->taxonRepository = $taxonRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('metodFronts', EntityType::class, [
                'class' => ProductGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $this->getProductGroupQb($er, Taxon::TAXON_FACADE_METOD);
                },
                'choice_attr' => function (ProductGroup $productGroup) {
                    return $this->getChoiceAttr($productGroup);
                },
                'label' => 'app.form.customer_project.product',
                'placeholder' => 'app.form.customer_project.product_placeholder',
            ])
            ->add('paxFronts', EntityType::class, [
                'class' => ProductGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $this->getProductGroupQb($er, Taxon::TAXON_FACADE_PAX);
                },
                'choice_attr' => function (ProductGroup $productGroup) {
                    return $this->getChoiceAttr($productGroup);
                },
                'label' => 'app.form.customer_project.product',
                'placeholder' => 'app.form.customer_project.product_placeholder',
            ])
            ->add('paints', EntityType::class, [
                'class' => ProductGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $this->getProductGroupQb($er, Taxon::TAXON_PAINT_CODE);
                },
                'choice_attr' => function (ProductGroup $productGroup) {
                    return $this->getChoiceAttr($productGroup);
                },
                'label' => 'app.form.customer_project.product',
                'placeholder' => 'app.form.customer_project.product_placeholder',
            ])
            ->add('accessoriesHandles', EntityType::class, [
                'class' => ProductGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $this->getProductGroupQb($er, Taxon::TAXON_ACCESSORY_HANDLE_CODE);
                },
                'choice_attr' => function (ProductGroup $productGroup) {
                    return $this->getChoiceAttr($productGroup);
                },
                'label' => 'app.form.customer_project.product',
                'placeholder' => 'app.form.customer_project.product_placeholder',
            ])
            ->add('accessories', EntityType::class, [
                'class' => ProductGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $this->getProductGroupQb($er, Taxon::TAXON_ACCESSORY_CODE);
                },
                'choice_attr' => function (ProductGroup $productGroup) {
                    return $this->getChoiceAttr($productGroup);
                },
                'label' => 'app.form.customer_project.product',
                'placeholder' => 'app.form.customer_project.product_placeholder',
            ])
            ->add('taps', EntityType::class, [
                'class' => ProductGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $this->getProductGroupQb($er, Taxon::TAXON_TAP_CODE);
                },
                'choice_attr' => function (ProductGroup $productGroup) {
                    return $this->getChoiceAttr($productGroup);
                },
                'label' => 'app.form.customer_project.product',
                'placeholder' => 'app.form.customer_project.product_placeholder',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_add_project_item_choices';
    }

    private function getChoiceAttr(ProductGroup $productGroup): array
    {
        // Get group product options and filter only available options for project item
        $groupOptionsCodes = array_filter(array_map(static function (ProductOption $option) {
            return $option->getCode();
        }, $productGroup->getProductsOptions()), static function ($code) {
            return in_array($code, ProjectItem::AVAILABLE_OPTION_CODES, true);
        });

        if (empty($groupOptionsCodes)) {
            $groupOptionsCodes = ['variant'];
        }

        return [
            'data-options' => implode(',', $groupOptionsCodes),
            'data-taxons' => implode(',', array_map(static function (Taxon $taxon) {
                return $taxon->getCode();
            }, $productGroup->getProductsTaxons())),
        ];
    }

    private function getProductGroupQb(EntityRepository $er, string $taxonCode): QueryBuilder
    {
        return $er->createQueryBuilder('g')
            ->innerJoin('g.products', 'p')
            ->where('g.mainTaxon = :mainTaxon')
            ->andWhere('g.products is not empty')
            ->andWhere('p.enabled = true')
            ->orderBy('g.position', 'ASC')
            ->setParameter('mainTaxon', $this->taxonRepository->findOneBy(['code' => $taxonCode]));
    }
}
