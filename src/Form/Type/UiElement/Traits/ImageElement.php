<?php

declare(strict_types=1);

namespace App\Form\Type\UiElement\Traits;

use App\Uploader\UploadedFile;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Constraints\RichEditorConstraints;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

trait ImageElement
{
    public function addImage(FormBuilderInterface $builder, string $baseName = 'image', array $options = []): void
    {
        // file
        $builder->add($baseName, FileType::class, [
            'label' => 'app.form.media.default.image',
            'required' => $options['required'] ?? true,
            'data_class' => null,
            'attr' => [
                'class' => $options['class'] ?? '',
                'data-slim' => true,
            ],
            'row_attr' => [
                // updated when upload... why ?!
                'class' => 'slim ' . ($options['row-class'] ?? ''),
                'data-jpeg-compression' => 80,
                'data-upload-base64' => false,
                'data-force-type' => 'jpg',
            ],
        ]);

        $constraints = ($options['required'] ?? true) ? [
            new NotBlank(),
            new Length(['max' => 100]),
        ] : [
            new Length(['max' => 100]),
        ];
        // alternative text
        $builder->add($baseName . '_alt', TextType::class, [
            'label' => 'app.form.media.default.image_alt',
            'required' => $options['required'] ?? true,
            'constraints' => $constraints,
        ]);

        // title
        $builder->add($baseName . '_title', TextType::class, [
            'label' => 'app.form.media.default.image_title',
            'required' => $options['required'] ?? true,
            'constraints' => $constraints,
        ]);

        // add events
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($baseName): void {
            $data = $event->getData();
            $base64Content = $data[$baseName] ?? null;
            if (! is_string($base64Content)) {
                return;
            } elseif (strlen($base64Content) === 0) {
                unset($data[$baseName]);
            } else {
                $uploadedFile = $this->transformBase64ToUploadedFile($base64Content);
                if ($uploadedFile) {
                    $data[$baseName] = $uploadedFile;
                    $event->setData($data);
                }
            }

            // Change image field constraints depending on submitted value
            $options = $event->getForm()->get($baseName)->getConfig()->getOptions();
            if (($options['required'] ?? true) && empty($data[$baseName] ?? null)) {
                $options['constraints'] = RichEditorConstraints::getImageConstraints($data, $baseName);
                $event->getForm()->add($baseName, FileType::class, $options);
            }
        });
    }

    private function transformBase64ToUploadedFile(string $base64Content): ?UploadedFile
    {
        $uploadedFile = null;
        // convert base64 image to UploadedFile
        $isJson = is_string($base64Content)
            && is_array(json_decode($base64Content, true))
            && json_last_error() === JSON_ERROR_NONE;
        $jsonDecode = $isJson ? json_decode($base64Content, true) : [];
        $imageBase64 = isset($jsonDecode['output']['image']) ? $jsonDecode['output']['image'] : '';
        if ($imageBase64 && preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
            $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);
            $type        = strtolower($type[1]); // jpg, png, gif
            $content     = base64_decode($imageBase64);
            $path        = tempnam(sys_get_temp_dir(), 'slim');
            file_put_contents($path, $content);
            $uploadedFile = new UploadedFile(
                $path,
                $jsonDecode['output']['name'],
                $jsonDecode['output']['type'],
            );
        }

        return $uploadedFile;
    }
}
