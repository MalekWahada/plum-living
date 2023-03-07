<?php

declare(strict_types=1);

namespace App\Form\Type\Filter;

use App\Model\ProductChannelFilter\ProductChannelFilterModel;
use App\Provider\Channel\ProductChannelFilterProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductChannelFilterType extends AbstractType
{
    private ProductChannelFilterProvider $channelProvider;

    public function __construct(ProductChannelFilterProvider $channelProvider)
    {
        $this->channelProvider = $channelProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('channel', ChoiceType::class, [
            'label' => false,
            'required' => false,
            'placeholder' => false,
            'choices' => $this->channelProvider->getChannels(),
            'choice_label' => fn (ProductChannelFilterModel $channelFilterModel): string => $channelFilterModel->getValue(),
            'choice_value' => fn (?ProductChannelFilterModel $channelModel): string => $channelModel !== null ? $channelModel->getCode() : '',
        ]);
    }
}
