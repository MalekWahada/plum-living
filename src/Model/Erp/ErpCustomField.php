<?php

declare(strict_types=1);

namespace App\Model\Erp;

abstract class ErpCustomField
{
    public const PRODUCT_CATEGORY = 'custitem_plum_product_category';
    public const PRODUCT_SUB_CATEGORY = 'custitem_plum_product_sub_category';
    public const FAMILY = 'custitem_plum_pf_family';
    public const SPECS_WIDTH = 'custitem_prq_tech_spec_width_mm';
    public const SPECS_HEIGHT = 'custitem_prq_tech_spec_height_mm';
    public const SPECS_DEPTH = 'custitem_prq_tech_spec_depth_mm';
    public const HANDLE_DIMENSION = 'custitem_plum_handle_dimension';
    public const HANDLE_FINISH = 'custitem_plum_handle_finition';
    public const HANDLE_DESIGN = 'custitem_plum_handle_design';
    public const AVAILABLE_FOR_SALE = 'custitem_prq_available_for_sale';
    public const OPTION_FINISH = 'custitem_plum_pf_finition';
    public const OPTION_HANDLE_FINISH = 'custitem_plum_handle_finition';
    public const OPTION_DESIGN = 'custitem_plum_pf_design';
    public const OPTION_HANDLE_DESIGN = 'custitem_plum_handle_design';
    public const OPTION_COLOR = 'custitem_prq_couleur_matrix_options';
    public const DIMENSION = 'custitem_plum_pf_dimension';
}
