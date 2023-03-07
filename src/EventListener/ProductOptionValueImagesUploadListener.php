<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Product\ProductOption;
use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductOptionValueImage;
use Sylius\Component\Core\Model\ImagesAwareInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class ProductOptionValueImagesUploadListener
{
    private ImageUploaderInterface $uploader;

    public function __construct(ImageUploaderInterface $uploader)
    {
        $this->uploader = $uploader;
    }

    public function uploadImages(GenericEvent $event): void
    {
        /** @var ProductOption $productOption */
        $productOption = $event->getSubject();
        /** @var ProductOptionValue[] $productOptionValues */
        $productOptionValues = $productOption->getValues();
        foreach ($productOptionValues as $productOptionValue) {
            Assert::isInstanceOf($productOptionValue, ImagesAwareInterface::class);
            /** @var ProductOptionValueImage $productOptionValueImage */
            foreach ($productOptionValue->getImages() as $productOptionValueImage) {
                $this->uploader->upload($productOptionValueImage);
            }
        }
    }
}
