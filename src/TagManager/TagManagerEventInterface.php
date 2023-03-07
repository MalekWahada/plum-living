<?php

declare(strict_types=1);

namespace App\TagManager;

interface TagManagerEventInterface
{
    public const ADD_TO_CART_EVENT = "addToCart";
    public const SHOW_PRODUCT_MODAL_EVENT = "showProductModal";
    public const ORDER_PAID_WITH_SAMPLE_EVENT = "orderPaidWithSample";
    public const ORDER_PAID_WITH_FRONT_SAMPLE_EVENT = "orderPaidWithFrontSample";
    public const ORDER_PAID_WITH_PAINT_SAMPLE_EVENT = "orderPaidWithPaintSample";
    public const ORDER_PAID_WITHOUT_SAMPLE_EVENT = "orderPaidWithoutSample";
    public const BACK_TO_SCANNER_AND_OPEN_PROJECT_EVENT = "backToScannerAndReopenProject";
    public const ASK_FOR_QUOTE_MONITORING_EVENT = "askForQuote";
    public const CONCEPTION_SERVICE_STARTED_EVENT = "conceptionServiceStarted";
    public const CONCEPTION_SERVICE_COMPLETED_EVENT = "conceptionServiceCompleted";
}
