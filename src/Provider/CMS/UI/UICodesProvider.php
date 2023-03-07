<?php

declare(strict_types=1);

namespace App\Provider\CMS\UI;

use App\Model\CMSFilter\ListingInspirationFilterModel;
use App\Model\CMSFilter\ListingProjectFilterModel;

class UICodesProvider
{
    const MONSIEUR_BIZ_UI_TEXT = 'monsieurbiz.text';
    const MONSIEUR_BIZ_UI_IMAGES = 'monsieurbiz.image_collection';
    const MONSIEUR_BIZ_UI_H3 = 'monsieurbiz.h3';
    const MONSIEUR_BIZ_UI_BUTTON = 'monsieurbiz.button';

    const APP_UI_ACCORDION = 'app.accordion';
    const APP_UI_CROSS_CONTENT = 'app.pages_listing';

    private const APP_UI_COLOR_CODE = 'app.color_ui';
    private const APP_UI_PIECE_CODE = 'app.project_piece';
    private const APP_UI_CHIP_CODE = 'app.chip';

    public function getFormattedContents(object $objectModel): array
    {
        if ($objectModel instanceof ListingProjectFilterModel) {
            return [
                $this->getContentStr(
                    self::APP_UI_PIECE_CODE,
                    'piece',
                    $objectModel->getPiece()
                ),
                $this->getContentStr(
                    self::APP_UI_COLOR_CODE,
                    'color',
                    $objectModel->getColor()
                ),
            ];
        } elseif ($objectModel instanceof ListingInspirationFilterModel) {
            return [
                $this->getContentStr(
                    self::APP_UI_CHIP_CODE,
                    'chip',
                    $objectModel->getChip()
                )
            ];
        }
        return [];
    }

    /**
     * return UI element content search string.
     * Every UI element search keys are defined through ContentModel
     * and it has mainly this string format:
     * '{"code":"UiCode","data":{"DataKey":"ContentCode'"}}'.
     *
     * @param string $uiCode
     * @param string $dataKey
     * @param string|null $contentCode
     * @return string
     */
    private function getContentStr(string $uiCode, string $dataKey, ?string $contentCode): string
    {
        return '%"code":"' . $uiCode . '","data":{"' . $dataKey . '":"' . $contentCode . '%';
    }
}
