<?php

declare(strict_types=1);

namespace App\Form\Extension\UiElement;

use App\Provider\CMS\ImageCollection\ImageCollectionTypeProvider;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\UiElement\ImageCollectionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageCollectionCustomTypeExtension extends AbstractTypeExtension
{
    private ImageCollectionTypeProvider $imageCollectionTypeProvider;

    public function __construct(ImageCollectionTypeProvider $imageCollectionTypeProvider)
    {
        $this->imageCollectionTypeProvider = $imageCollectionTypeProvider;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ImageCollectionType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'label' => 'app.form.images_collection.label',
            'choices' => $this->imageCollectionTypeProvider->getImageCollectionTypeChoices(),
            'choice_label' => fn (string $label): string => 'app.ui.cms.image_collection.type_' . $label,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var array $data */
            $data = $event->getData();

            // limit MOSAIC images to three
            $imageOptions = $form->get('images')->getConfig()->getOptions();
            $imageOptions['constraints'] =
                array_key_exists('type', $data) &&
                $data['type'] === ImageCollectionTypeProvider::IMAGE_COLLECTION_TYPE_MOSAIC ?
                    [
                        new Assert\Count(['max' => 3]),
                    ] : [];

            $form->remove('images');
            $form->add('images', CollectionType::class, $imageOptions);
        });
    }
}
