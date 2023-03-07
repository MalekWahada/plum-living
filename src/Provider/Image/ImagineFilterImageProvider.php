<?php

declare(strict_types=1);

namespace App\Provider\Image;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class ImagineFilterImageProvider
{
    // plum-scanner combination filters
    const APP_FILTER_PS_FINAL_STEP_PRODUCT_THUMBNAIL = 'app_plum_scanner_final_step_product_thumbnail';
    const APP_FILTER_PS_FINAL_STEP_PRODUCT_THUMBNAIL_RETINA = 'app_plum_scanner_final_step_product_thumbnail_retina';
    // tunnel product modal filters
    const APP_FILTER_TUNNEL_MODAL_THUMBNAIL = 'app_tunnel_modal_thumbnail';
    const APP_FILTER_TUNNEL_MODAL_THUMBNAIL_RETINA = 'app_tunnel_modal_thumbnail_retina';

    private CacheManager $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function getImagePaths(string $imagePath, string $srcFilter, string $srcSetFilter): array
    {
        return [
            'src' => $this->cache->getBrowserPath($imagePath, $srcFilter),
            'srcset' => $this->cache->getBrowserPath($imagePath, $srcSetFilter),
        ];
    }
}
