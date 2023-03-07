<?php

declare(strict_types=1);

namespace App\Checker;

use App\Formatter\CMS\TextToArrayFormatter;
use App\Provider\CMS\PagesSkeleton\PagesSkeletonProvider;

final class ProductInfoContentChecker
{
    private PagesSkeletonProvider $skeletonProvider;

    public function __construct(PagesSkeletonProvider $skeletonProvider)
    {
        $this->skeletonProvider = $skeletonProvider;
    }

    public function checkProductInformation(string $content): bool
    {
        $formattedContent = TextToArrayFormatter::format($content);

        if (null === $formattedContent) {
            return false;
        }

        // The 'mosaic images collection' UI is optional in this type of content Product complete information can have no
        $uisSkeleton = $this->skeletonProvider->provideCMSSkeletonUIs(PagesSkeletonProvider::UI_SKELETON_PRODUCT_INFORMATION);

        return $this->checkUIsOrder($formattedContent, $uisSkeleton);
    }

    // Assure that the submitted UIs follow the UI skeleton order.
    // We might have a UIs count greater than the defined UI skeleton length or even less, for that:
    // - great than: we make sure at least the 1st UIs are set according to the defined UI skeleton.
    // - less than: we consider the content as valid as long the UIs match with the 1st UI skeleton items.
    private function checkUIsOrder(array $uis, array $uisSkeleton): bool
    {
        $uisSkeletonCount = count($uisSkeleton);
        $index = 0;

        /** @var array $ui */
        foreach ($uis as $ui) {
            if ($index >= $uisSkeletonCount) {
                break;
            }

            if (!$this->isUICodeValid($ui, $uisSkeleton, $index)) {
                return false;
            }
            $index++;
        }

        return true;
    }

    private function isUICodeValid(array $ui, array $uisSkeleton, int $index): bool
    {
        return isset($ui['code']) &&
            in_array($ui['code'], [...$uisSkeleton[$index]], true);
    }
}
