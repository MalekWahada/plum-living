<?php

declare(strict_types=1);

namespace App\Twig;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Entity\Locale\Locale;
use MonsieurBiz\SyliusRichEditorPlugin\Exception\UiElementNotFoundException;
use MonsieurBiz\SyliusRichEditorPlugin\Twig\RichEditorExtension;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CMSRichEditorExtension extends AbstractExtension
{
    public const TRANSLATION_SLUG_VARIABLE = '%trans_slug%';

    private RichEditorExtension $decorated;
    private SwitchableTranslationContextInterface $translationContext;

    public function __construct(
        RichEditorExtension $decorated,
        SwitchableTranslationContextInterface $translationContext
    ) {
        $this->decorated = $decorated;
        $this->translationContext = $translationContext;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('monsieurbiz_richeditor_render_field', [$this, 'renderField'], ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFilter('monsieurbiz_richeditor_render_elements', [$this, 'renderElements'], ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFilter('monsieurbiz_richeditor_render_element', [$this, 'renderElement'], ['is_safe' => ['html'], 'needs_context' => true]),
        ];
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('monsieurbiz_richeditor_list_elements', [$this, 'listUiElements'], ['is_safe' => ['html', 'js']]),
            new TwigFunction('monsieurbiz_richeditor_youtube_link', [$this, 'convertYoutubeEmbeddedLink'], ['is_safe' => ['html', 'js']]),
            new TwigFunction('monsieurbiz_richeditor_youtube_id', [$this, 'getYoutubeIdFromLink'], ['is_safe' => ['html', 'js']]),
            new TwigFunction('monsieurbiz_richeditor_get_elements', [$this, 'getElements'], ['is_safe' => ['html']]),
            new TwigFunction('monsieurbiz_richeditor_get_default_element', [$this, 'getDefaultElement'], ['is_safe' => ['html']]),
            new TwigFunction('monsieurbiz_richeditor_get_default_element_data_field', [$this, 'getDefaultElementDataField'], ['is_safe' => ['html']]),
            new TwigFunction('monsieurbiz_richeditor_get_current_file_path', [$this, 'getCurrentFilePath'], ['needs_context' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * Replace translation slug variable in all rendered CMS elements
     * @param string $content
     * @return string
     */
    public function replaceTranslationSlug(string $content): string
    {
        return str_replace(self::TRANSLATION_SLUG_VARIABLE, $this->translationContext->getSlug(), $content);
    }

    /**
     * @param string|null $content
     *
     * @return array
     */
    public function getElements(?string $content): array
    {
        return $this->decorated->getElements($content);
    }

    /**
     * @param array $context
     * @param string|null $content
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    public function renderField(array $context, ?string $content): string
    {
        return $this->replaceTranslationSlug($this->decorated->renderField($context, $content));
    }

    /**
     * @param array $context
     * @param array $elements
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return string
     */
    public function renderElements(array $context, array $elements): string
    {
        return $this->replaceTranslationSlug($this->decorated->renderElements($context, $elements));
    }

    /**
     * @param array $context
     * @param array $element
     *
     * @throws UiElementNotFoundException
     * @throws LoaderError [twig.render] When the template cannot be found
     * @throws SyntaxError [twig.render] When an error occurred during compilation
     * @throws RuntimeError [twig.render] When an error occurred during rendering
     *
     * @return string
     */
    public function renderElement(array $context, array $element): string
    {
        return $this->replaceTranslationSlug($this->decorated->renderElement($context, $element));
    }

    /**
     * List available Ui Elements in JSON.
     *
     * @return string
     */
    public function listUiElements(): string
    {
        return $this->decorated->listUiElements();
    }

    /**
     * Convert Youtube link to embed URL.
     *
     * @param string $url
     *
     * @return string|null
     */
    public function convertYoutubeEmbeddedLink(string $url): ?string
    {
        return $this->decorated->convertYoutubeEmbeddedLink($url);
    }

    /**
     * Retrieve the Youtube ID from a Youtube link.
     *
     * @param string $url
     *
     * @return string|null
     */
    public function getYoutubeIdFromLink(string $url): ?string
    {
        return $this->decorated->getYoutubeIdFromLink($url);
    }

    public function getDefaultElement(): string
    {
        return $this->decorated->getDefaultElement();
    }

    public function getDefaultElementDataField(): string
    {
        return $this->decorated->getDefaultElementDataField();
    }

    /**
     * @param array $context
     * @param string $varName
     * @return string|null
     */
    public function getCurrentFilePath(array $context, string $varName = 'full_name'): ?string
    {
        return $this->decorated->getCurrentFilePath($context, $varName);
    }
}
