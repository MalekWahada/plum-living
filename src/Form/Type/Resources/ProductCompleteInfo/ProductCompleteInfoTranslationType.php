<?php

declare(strict_types=1);

namespace App\Form\Type\Resources\ProductCompleteInfo;

use App\Entity\Product\ProductCompleteInfoTranslation;
use App\Factory\Product\ProductCompleteInfoTranslationFactory;
use App\Provider\CMS\UI\UITagsProvider;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\RichEditorType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductCompleteInfoTranslationType extends AbstractResourceType
{
    private ProductCompleteInfoTranslationFactory $infoTranslationFactory;

    public function __construct(
        ProductCompleteInfoTranslationFactory $infoTranslationFactory,
        string $dataClass,
        array $validationGroups = []
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->infoTranslationFactory = $infoTranslationFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', RichEditorType::class, [
            'required' => false,
            'label' => 'sylius.ui.content',
            'tags' => [UITagsProvider::TAG_PRODUCT_COMPLETE_INFO],
            'attr' => [
                'allowed_infos' => 100,
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ProductCompleteInfoTranslation|null $data */
            $data = $event->getData();
            if (null === $data || null === $data->getContent()) {
                $completeInfoTranslation = $this->infoTranslationFactory->createFromContent();
                $event->setData($completeInfoTranslation);
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'app_product_complete_info_translation';
    }
}
