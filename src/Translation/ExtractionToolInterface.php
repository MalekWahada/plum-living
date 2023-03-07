<?php

declare(strict_types=1);

namespace App\Translation;

interface ExtractionToolInterface
{
    public const CMS_PAGE_TYPE = 'cms_page';
    public const PRODUCT_COMPLETE_INFO_TYPE = 'product_complete_info';
    public const TAXON_TYPE = 'taxon';

    public const EXTRACTION_TOOL_TYPES = [
        self::CMS_PAGE_TYPE,
        self::PRODUCT_COMPLETE_INFO_TYPE,
        self::TAXON_TYPE
    ];
}
