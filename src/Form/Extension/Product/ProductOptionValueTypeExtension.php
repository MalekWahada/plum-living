<?php

declare(strict_types=1);

namespace App\Form\Extension\Product;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductOptionValueImage;
use App\Entity\Tunnel\Shopping\Combination;
use App\Form\Type\Resources\ProductOptionValueImageType;
use App\Provider\Image\ColorProvider;
use App\Repository\Tunnel\Shopping\CombinationRepository;
use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueType;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

final class ProductOptionValueTypeExtension extends AbstractTypeExtension
{
    private CombinationRepository $combinationRepository;
    private LocaleProviderInterface $localeProvider;
    private ColorProvider $colorProvider;
    private SerializerInterface $serializer;
    private Filesystem $filesystem;

    public function __construct(
        CombinationRepository $combinationRepository,
        LocaleProviderInterface $localeProvider,
        ColorProvider $colorProvider,
        SerializerInterface $serializer,
        Filesystem $filesystem
    ) {
        $this->combinationRepository = $combinationRepository;
        $this->localeProvider = $localeProvider;
        $this->colorProvider = $colorProvider;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('position', IntegerType::class, [
            'required' => false,
            'label' => 'app.form.product_value.position',
        ]);
        $builder->add('images', CollectionType::class, [
            'label' => 'app.form.product_value.image',
            'entry_type' => ProductOptionValueImageType::class,
            'button_add_label' => 'app.form.product_value.add_button_label',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
            'required' => false,
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $formEvent) {
            /**
             * @var ProductOptionValue $entity
             */
            $entity = $formEvent->getData();
            $form = $formEvent->getForm();
            // if we create a new option value we should not populate the combination field
            if ($entity !== null && $entity->getOption()->getCode() === ProductOption::PRODUCT_OPTION_FINISH) {
                $combinations = $this->combinationRepository->findByOptionValueCode($entity->getOptionCode(), $entity->getCode());
                // if there are no combinations for an existing productOptionValue we aren't interested to add a combination field
                // also if there are combination but having no recommendation field isn't created(the repository is handling that)
                if (count($combinations) > 0) {
                    $form->add('combinationLabel', TextType::class, [
                        'required' => false,
                        'label' => 'app.form.product_value.combination_label',
                    ]);
                    $form->add('combination', ChoiceType::class, [
                        'required' => false,
                        'label' => 'app.form.product_value.combination',
                        'placeholder' => 'app.form.product_value.combination_placeholder',
                        'choices' => $combinations,
                        'choice_label' => fn (Combination $combination): int => $combination->getId(),
                    ]);
                }
            }
        });
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $formEvent) {
            $colorHex = null;
            /** @var ProductOptionValue $productOptionValue */
            $productOptionValue = $formEvent->getData();
            // teh option code isn't set for the new option value we must hav a check
            if ($productOptionValue->getOption() !== null) {
                if ($productOptionValue->getOption()->getCode() === ProductOption::PRODUCT_OPTION_FINISH) {
                    $colorHex = $this->getColorHex($productOptionValue, ProductOptionValueImage::PRODUCT_OPTION_VALUE_IMAGE_TYPE_DISPLAY);
                } elseif ($productOptionValue->getOption()->getCode() === ProductOption::PRODUCT_OPTION_COLOR) {
                    $colorHex = $this->getColorHex($productOptionValue, ProductOptionValueImage::PRODUCT_OPTION_VALUE_IMAGE_TYPE_DEFAULT);
                }
            }
            $productOptionValue->setColorHex($colorHex);
        });
    }

    /**
     * get color Hexadecimal value from an existing image path or from a new submitted image
     * @param ProductOptionValue $productOptionValue
     * @param string $type
     * @return string|null
     */
    private function getColorHex(ProductOptionValue $productOptionValue, string $type): ?string
    {
        $image = $productOptionValue->getImagesByType($type)->first();
        if ($image instanceof ProductOptionValueImage) {
            $path = $image->getFile() !== null ? $image->getFile()->getPathname() : 'media/image/' . $image->getPath();
            return $this->filesystem->exists($path) ? $this->colorProvider->getImageColor($path) : null;
        }
        return null;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', ProductOptionValue::class);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductOptionValueType::class];
    }
}
