<?php

declare(strict_types=1);

namespace App\Controller\PlumStyler;

use App\Entity\Product\ProductOptionValue;
use App\Entity\Product\ProductOptionValueImage;
use App\Repository\Product\ProductOptionValueRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class StylerOptionsController extends AbstractController
{
    private const OPTION_VALUE_IMAGE_FILTER = 'app_plum_styler_option_value_image';

    private LocaleContextInterface $localeContext;
    private CacheManager $cacheManager;
    private ProductOptionValueRepository $repository;
    private KernelInterface $kernel;

    public function __construct(
        ProductOptionValueRepository $productOptionValueRepository,
        LocaleContextInterface $localeContext,
        CacheManager $cacheManager,
        KernelInterface $kernel
    ) {
        $this->repository = $productOptionValueRepository;
        $this->localeContext = $localeContext;
        $this->cacheManager = $cacheManager;
        $this->kernel = $kernel;
    }

    /**
     * Export product options and values for styler
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        /** @var ProductOptionValue[] $values */
        $values = $this->repository->findAll();
        $localeCode = $this->localeContext->getLocaleCode();
        $result = [];

        foreach ($values as $value) {
            $images = $value->getImagesByType(ProductOptionValueImage::PRODUCT_OPTION_VALUE_IMAGE_TYPE_STYLER);
            $image = $images->first() ?: null;

            $result[] = [
                'code' => $value->getCode(),
                'name' => $value->getTranslation($localeCode)->getValue(),
                'colorHex' => $value->getColorHex(),
                'image' => null !== $image ? $this->cacheManager->generateUrl($image->getPath(), self::OPTION_VALUE_IMAGE_FILTER): null
            ];
        }

        return $this->json($result, 200, $this->kernel->isDebug() ? [ // Allow CORS for debug only
            'Access-Control-Allow-Origin' => '*',
        ] : [], [
            JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES, // remove url backslashes
        ]);
    }
}
