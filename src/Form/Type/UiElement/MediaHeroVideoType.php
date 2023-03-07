<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement;

use App\Form\Type\UiElement\Traits\LinkElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use MonsieurBiz\SyliusRichEditorPlugin\Validator\Constraints\YoutubeUrl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MediaHeroVideoType extends AbstractType
{
    use LinkElement;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // title
        $builder->add('title', TextType::class, [
            'label' => 'app.form.media.default.title',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        // description
        $builder->add('description', WysiwygType::class, [
            'label' => 'app.form.media.default.description',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        // video youtube
        $builder->add('video', UrlType::class, [
            'label' => 'app.form.media.default.video',
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new YoutubeUrl(),
            ],
        ]);
    }
}
