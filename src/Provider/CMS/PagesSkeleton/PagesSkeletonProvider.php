<?php

declare(strict_types=1);

namespace App\Provider\CMS\PagesSkeleton;

use App\Provider\CMS\UI\UICodesProvider;

final class PagesSkeletonProvider
{
    public const ARTICLE_CHIP = 'article_chip';
    public const ARTICLE_MAIN_TITLE = 'article_main_title';
    public const ARTICLE_SHORT_DESC = 'article_short_desc';
    public const ARTICLE_READ_BUTTON = 'article_read_button';
    public const ARTICLE_CONTENT = 'article_content';
    public const ARTICLE_DISCOVER_OTHER_PROJECTS_TITLE = 'article_discover_other_projects_title';
    public const ARTICLE_DISCOVER_OTHER_PROJECTS_BUTTON = 'article_discover_other_projects_button';
    public const ARTICLE_OTHER_PAGES_TITLE = 'article_other_pages_title';
    public const ARTICLE_OTHER_PAGES_BACK_BUTTON = 'article_other_pages_back_button';
    public const ARTICLE_OTHER_PAGES = 'article_other_pages';


    public const PROJECT_MAIN_TITLE = 'project_main_title';
    public const PROJECT_COLOR = 'project_color';
    public const PROJECT_PIECE_TYPE = 'project_type';
    public const PROJECT_SHORT_DESC = 'project_short_desc';
    public const PROJECT_SLIDER_COLLECTION = 'project_slider_collection';
    public const PROJECT_DISCOVER_BUDGET_BUTTON = 'project_discover_budget_button';
    public const PROJECT_LONG_DESC = 'project_long_desc';
    public const PROJECT_BUDGET = 'project_budget';
    public const PROJECT_SLIDER_PLANS = 'project_slider_plans';
    public const PROJECT_PLAN_FILE = 'project_plan_file';
    public const PROJECT_DISCOVER_TUTORIAL_LINK = 'project_discover_tutorial_link';
    public const PROJECT_MOSAIC_COLLECTION = 'project_mosaic_collection';
    public const PROJECT_ESTIMATE_MY_PROJECT_TITLE = 'project_estimate_my_project_title';
    public const PROJECT_ESTIMATE_MY_PROJECT_BUTTON = 'project_estimate_my_project_button';
    public const PROJECT_OTHER_PAGES_TITLE = 'project_other_pages_title';
    public const PROJECT_OTHER_PAGES_BACK_BUTTON = 'project_other_pages_back_button';
    public const PROJECT_OTHER_PAGES = 'project_other_pages';


    public const CONCEPT_MAIN_TITLE = 'concept_main_title';
    public const CONCEPT_MAIN_DESC = 'concept_main_desc';
    public const CONCEPT_SCHEMA_IMAGE = 'concept_schema_image';
    public const CONCEPT_STEPS_MAIN_TITLE = 'concept_steps_title';
    public const CONCEPT_STEPS_IMAGES = 'concept_steps_images';
    public const CONCEPT_TUTORIAL_TITLE = 'concept_tutorial_title';
    public const CONCEPT_TUTORIAL_DESC = 'concept_tutorial_desc';
    public const CONCEPT_TUTORIAL_DISCOVER_ARTICLE_LINK = 'concept_tutorial_discover_article_link';
    public const CONCEPT_TUTORIAL_CHECK_FAQ = 'concept_tutorial_check_faq';
    public const CONCEPT_TUTORIAL_VIDEO = 'concept_tutorial_video';
    public const CONCEPT_TUTORIAL_DESIGN_FINISH_BUTTON = 'concept_tutorial_design_finish_button';
    public const CONCEPT_OTHER_PAGES_TITLE = 'concept_other_pages_title';
    public const CONCEPT_OTHER_PAGES = 'concept_other_pages';
    public const CONCEPT_OTHER_PAGES_DISCOVER_BUTTON = 'concept_other_pages_discover_button';
    public const CONCEPT_INSPIRATION_TITLE = 'concept_inspiration_title';
    public const CONCEPT_INSPIRATION_MOSAIC_IMAGES = 'concept_inspiration_mosaic_images';


    public const DESIGN_FINISH_MAIN_TITLE = 'design_finish_main_title';
    public const DESIGN_FINISH_OPTION_DESIGN_TITLE = 'design_finish_option_design_title';
    public const DESIGN_FINISH_OPTION_DESIGN_CODE = 'design_finish_option_design_code';
    public const DESIGN_FINISH_OPTION_FINISH_TITLE = 'design_finish_option_finish_title';
    public const DESIGN_FINISH_OPTION_FINISH_CODE = 'design_finish_option_finish_code';
    public const DESIGN_FINISH_OPTION_COLOR_TITLE = 'design_finish_option_color_title';
    public const DESIGN_FINISH_OPTION_COLOR_CODE = 'design_finish_option_color_code';
    public const DESIGN_FINISH_SEE_SAMPLE_TEXT = 'design_finish_see_sample_desc';
    public const DESIGN_FINISH_SEE_SAMPLE_LINK = 'design_finish_see_sample_link';
    public const DESIGN_FINISH_SEE_SAMPLE_IMAGE = 'design_finish_see_sample_image';
    public const DESIGN_FINISH_OTHER_PAGES_TITLE = 'design_finish_other_pages_title';
    public const DESIGN_FINISH_OTHER_PAGES = 'design_finish_other_pages';
    public const DESIGN_FINISH_DISCOVER_OTHER_PAGES_BUTTON = 'design_finish_discover_other_pages_button';
    public const DESIGN_FINISH_MOSAIC_IMAGES_TITLE = 'design_finish_mosaic_images_title';
    public const DESIGN_FINISH_MOSAIC_IMAGES = 'design_finish_mosaic_images';


    public const HOME_MAIN_SLIDER = 'home_main_slider';
    public const HOME_MAIN_TITLE = 'home_main_title';
    public const HOME_IMAGES_STEPS_DESC = 'home_images_steps_desc';
    public const HOME_IMAGES_STEPS = 'home_images_steps';
    public const HOME_DISCOVER_CONCEPT_BUTTON = 'home_discover_concept_button';
    public const HOME_OPTION_COLOR_TITLE = 'home_option_color_title';
    public const HOME_OPTION_COLOR_CODE = 'home_option_color_code';
    public const HOME_DESIGN_FINISH_BUTTON = 'home_design_finish_button';
    public const HOME_ORDER_BUTTON = 'home_order_button';
    public const HOME_OTHER_PAGES_TITLE = 'home_other_pages_title';
    public const HOME_OTHER_PAGES = 'home_other_pages';
    public const HOME_DISCOVER_OTHER_PAGES_BUTTON = 'home_discover_other_pages_button';
    public const HOME_MANUFACTURING_TITLE = 'home_manufacturing_title';
    public const HOME_MANUFACTURING_DESC = 'home_manufacturing_desc';
    public const HOME_MANUFACTURING_BUTTON = 'home_manufacturing_button';
    public const HOME_MANUFACTURING_IMAGES = 'home_manufacturing_images';
    public const HOME_MOSAIC_IMAGES_TITLE = 'home_mosaic_images_title';
    public const HOME_MOSAIC_IMAGES = 'home_mosaic_images';
    public const HOME_CREATION_BANNER = 'home_creation_banner';
    public const HOME_SCAN_BANNER = 'home_scan_banner';



    public const QUOTATION_HOME_MAIN_TITLE = 'quotation_home_main_title';
    public const QUOTATION_HOME_PAGES_CARDS = 'quotation_home_pages_cards';
    public const QUOTATION_HOME_CARDS = 'quotation_home_cards';
    public const QUOTATION_HOME_BUTTON = 'quotation_home_button';
    public const QUOTATION_HOME_CROSS_CONTENT_TITLE = 'quotation_home_cross_content_title';
    public const QUOTATION_HOME_CROSS_CONTENT_BACK_LINK = 'quotation_home_cross_content_back_link';
    public const QUOTATION_HOME_CROSS_CONTENT_PAGES = 'quotation_home_cross_content_pages';

    public const FIND_INSTALLER_MAIN_TITLE = 'find_installer_main_title';
    public const FIND_INSTALLER_CROSS_CONTENT_TITLE = 'find_installer_cross_content_title';
    public const FIND_INSTALLER_CROSS_CONTENT_BACK_LINK = 'find_installer_cross_content_back_link';
    public const FIND_INSTALLER_CROSS_CONTENT_PAGES = 'find_installer_cross_content_pages';

    public const CONCEPTION_HOME_BUTTON = 'conception_home_button';
    public const CONCEPTION_HOME_CROSS_CONTENT_TITLE = 'conception_home_cross_content_title';
    public const CONCEPTION_HOME_CROSS_CONTENT_BACK_LINK = 'conception_home_cross_content_back_link';
    public const CONCEPTION_HOME_CROSS_CONTENT_PAGES = 'conception_home_cross_content_pages';

    public const TERRA_CLUB_CROSS_CONTENT_TITLE = 'terra_club_cross_content_title';
    public const TERRA_CLUB_CROSS_CONTENT_BACK_LINK = 'terra_club_cross_content_back_link';
    public const TERRA_CLUB_CROSS_CONTENT_PAGES = 'terra_club_cross_content_pages';

    public const PRODUCT_COMPLETE_INFO_DETAIL = 'product_complete_info_detail';
    public const PRODUCT_COMPLETE_INFO_QUESTIONS = 'product_complete_info_questions';
    public const PRODUCT_COMPLETE_INFO_MOSAIC_IMAGES = 'product_complete_info_mosaic_images';
    public const PRODUCT_COMPLETE_INFO_EXAMPLES_TITLE = 'product_complete_info_examples_title';
    public const PRODUCT_COMPLETE_INFO_EXAMPLES_BUTTON_LINK = 'product_complete_info_examples_button_link';
    public const PRODUCT_COMPLETE_INFO_CROSS_CONTENT_TITLE = 'product_complete_info_cross_content_title';
    public const PRODUCT_COMPLETE_INFO_INSPIRATIONS_BUTTON_LINK = 'product_complete_info_inspirations_button_link';
    public const PRODUCT_COMPLETE_INFO_CROSS_CONTENT = 'product_complete_info_cross_content';


    // if logic is validated it will applied on the article + project CMS pages
    const UI_SKELETON_PAGE_ARTICLE = 'content_page_article';
    const UI_SKELETON_PAGE_PROJECT = 'content_page_project';
    const UI_SKELETON_PRODUCT_INFORMATION = 'content_product_information';

    const SKELETON_UI_TYPES = [
        self::UI_SKELETON_PRODUCT_INFORMATION,
    ];

    /**
     * for a UI skeleton of type self::UI_SKELETON_PRODUCT_INFORMATION the mosaic images collection is optional.
     *
     * @param string $skeletonType
     * @return array|array[]
     */
    public function provideCMSSkeletonUIs(string $skeletonType): array
    {
        if ($skeletonType === self::UI_SKELETON_PRODUCT_INFORMATION) {
            return [
                [UICodesProvider::MONSIEUR_BIZ_UI_TEXT],
                [UICodesProvider::APP_UI_ACCORDION],
                [UICodesProvider::MONSIEUR_BIZ_UI_IMAGES, UICodesProvider::MONSIEUR_BIZ_UI_H3],
                [UICodesProvider::MONSIEUR_BIZ_UI_H3, UICodesProvider::MONSIEUR_BIZ_UI_BUTTON],
                [UICodesProvider::MONSIEUR_BIZ_UI_BUTTON, UICodesProvider::MONSIEUR_BIZ_UI_H3],
                [UICodesProvider::MONSIEUR_BIZ_UI_H3, UICodesProvider::MONSIEUR_BIZ_UI_BUTTON],
                [UICodesProvider::MONSIEUR_BIZ_UI_BUTTON, UICodesProvider::APP_UI_CROSS_CONTENT],
                [UICodesProvider::APP_UI_CROSS_CONTENT],
            ];
        }

        return [];
    }
}
