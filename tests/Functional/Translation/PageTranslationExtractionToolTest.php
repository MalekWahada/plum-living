<?php

declare(strict_types=1);

namespace App\Tests\Functional\Translation;

use App\Entity\Page\Page;
use App\Exception\Translation\TranslationException;
use App\Model\Translation\TranslationBag;
use App\Repository\Page\PageRepository;
use App\Tests\DatabaseAwareTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Translation\PageTranslationExtractionTool;

class PageTranslationExtractionToolTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use DatabaseAwareTrait;

    private PageTranslationExtractionTool $extractor;
    private RepositoryInterface $repository;

    protected function setUp(): void
    {
        static::$dbPopulated = true; // DB is pre-populated by command line. No need to populate it again.
        $kernel = self::bootKernel();
        $this->initDatabase($kernel);
        $this->extractor = self::$container->get(PageTranslationExtractionTool::class);
        $this->repository = self::$container->get('monsieurbiz_cms_page.repository.page');
    }

    /**
     * @throws TranslationException
     * @dataProvider provideRefKeys
     */
    public function testExtractReferenceLocaleTranslations(string $pageCode, array $keys): void
    {
        $page = $this->repository->findOneBy(['code' => $pageCode]);
        self::assertNotNull($page);

        $bag = new TranslationBag();
        $this->extractor->extractReferenceLocaleTranslations($bag, $page);
        foreach ($keys as $key => $value) {
            self::assertTrue($bag->has($key), sprintf('Missing key "%s" for reference language on page "%s"', $key, $pageCode));
            self::assertNotEquals($bag->getKey($key)->getValue($page->getReferenceLocaleCode()), $value);
        }
    }

    /**
     * @throws TranslationException
     * @dataProvider provideRefKeys
     */
    public function testApplyTranslations(string $pageCode, array $keys): void
    {
        $page = $this->repository->findOneBy(['code' => $pageCode]);
        self::assertNotNull($page);

        $bag = new TranslationBag();

        foreach ($keys as $key => $value) {
            $bag->add($key, $value, $page->getReferenceLocaleCode(), null, $pageCode);
        }
        $this->extractor->applyTranslations($bag, $page, $page->getReferenceLocaleCode());

        $translatedBag = new TranslationBag();
        $this->extractor->extractReferenceLocaleTranslations($translatedBag, $page);
        foreach ($keys as $key => $value) {
            $transKey = $translatedBag->getKey($key);
            self::assertNotNull($transKey, sprintf('Missing key "%s" for reference language on page "%s"', $key, $pageCode));

            $transValue = $transKey->getValue($page->getReferenceLocaleCode());
            self::assertEquals($transValue, $value, sprintf('Key "%s" have not been translated to "%s" (actual "%s")', $key, $value, $transValue));
        }
    }

    public function provideRefKeys(): \Generator
    {
        yield [
            "ikea-mode-d'emploi",
            [
                "ikea-mode-d'emploi.slug" => "new-value",
                "ikea-mode-d'emploi.title" => "__new_value__",
                "ikea-mode-d'emploi.meta_title" => "__new_value__",
                "ikea-mode-d'emploi.meta_description" => "__new_value__",
                "ikea-mode-d'emploi.meta_keywords" => "__new_value__",
                "ikea-mode-d'emploi.app_media_hero.0.title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_hero.0.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_hero.0.image_alt" => "__new_value__",
                "ikea-mode-d'emploi.app_media_hero.0.image_title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_text_only.0.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.0.title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.0.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.0.image_alt" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.0.image_title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.0.title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.0.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.0.image_alt" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.0.image_title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.1.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.1.image_alt" => "__new_value__",
                "ikea-mode-d'emploi.app_media_two_hybrid_columns.1.image_title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.1.title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.1.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.1.image_alt" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.1.image_title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.2.title" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.2.description" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.2.image_alt" => "__new_value__",
                "ikea-mode-d'emploi.app_media_panoramic_photo_xl.2.image_title" => "__new_value__"
            ]
        ];
        yield [
            "home-page",
            [
                "home-page.slug" => "new-value",
                "home-page.title" => "__new_value__",
                "home-page.meta_title" => "__new_value__",
                "home-page.meta_description" => "__new_value__",
                "home-page.meta_keywords" => "__new_value__",
                "home-page.app_custom_slider.0.images.0.alt" => "__new_value__",
                "home-page.app_custom_slider.0.images.0.title" => "__new_value__",
                "home-page.app_custom_slider.0.images.0.image_title" => "__new_value__",
                "home-page.app_custom_slider.0.images.0.image_category" => "__new_value__",
                "home-page.app_custom_slider.0.images.1.alt" => "__new_value__",
                "home-page.app_custom_slider.0.images.1.title" => "__new_value__",
                "home-page.app_custom_slider.0.images.1.image_title" => "__new_value__",
                "home-page.app_custom_slider.0.images.1.image_category" => "__new_value__",
                "home-page.app_custom_slider.0.images.2.alt" => "__new_value__",
                "home-page.app_custom_slider.0.images.2.title" => "__new_value__",
                "home-page.app_custom_slider.0.images.2.image_title" => "__new_value__",
                "home-page.app_custom_slider.0.images.2.image_category" => "__new_value__",
                "home-page.app_custom_slider.0.images.3.alt" => "__new_value__",
                "home-page.app_custom_slider.0.images.3.title" => "__new_value__",
                "home-page.app_custom_slider.0.images.3.image_title" => "__new_value__",
                "home-page.app_custom_slider.0.images.3.image_category" => "__new_value__",
                "home-page.monsieurbiz_h1.0.content" => "__new_value__",
                "home-page.monsieurbiz_text.0.content" => "__new_value__",
                "home-page.app_images_steps.0.imagesSteps.0.titleStep" => "__new_value__",
                "home-page.app_images_steps.0.imagesSteps.0.descriptionStep" => "__new_value__",
                "home-page.app_images_steps.0.imagesSteps.1.titleStep" => "__new_value__",
                "home-page.app_images_steps.0.imagesSteps.1.descriptionStep" => "__new_value__",
                "home-page.app_images_steps.0.imagesSteps.2.titleStep" => "__new_value__",
                "home-page.app_images_steps.0.imagesSteps.2.descriptionStep" => "__new_value__",
                "home-page.monsieurbiz_button.0.label" => "__new_value__",
                "home-page.monsieurbiz_h3.0.content" => "__new_value__",
                "home-page.monsieurbiz_button.1.label" => "__new_value__",
                "home-page.monsieurbiz_button.2.label" => "__new_value__",
                "home-page.monsieurbiz_h2.0.content" => "__new_value__",
                "home-page.monsieurbiz_button.3.label" => "__new_value__",
                "home-page.monsieurbiz_h3.1.content" => "__new_value__",
                "home-page.monsieurbiz_text.1.content" => "__new_value__",
                "home-page.monsieurbiz_button.4.label" => "__new_value__",
                "home-page.monsieurbiz_h2.1.content" => "__new_value__"
            ]
        ];
    }
}
