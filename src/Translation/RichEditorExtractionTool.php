<?php

declare(strict_types=1);

namespace App\Translation;

use App\Erp\Slugifier;
use Psr\Log\LoggerInterface;

abstract class RichEditorExtractionTool implements ExtractionToolInterface
{
    protected const RICH_EDITOR_CONTENT_TRANSLATABLE_DATA_FIELDS = [
        'alt',
        'caption',
        'caption1',
        'caption2',
        'cardMainTitle',
        'cardTotalDetails',
        'cardTotalTitle',
        'citation',
        'content',
        'description',
        'description1',
        'description2',
        'descriptionStep',
        'detail',
        'hint',
        'image_alt',
        'image_category',
        'image_description',
        'image_title',
        'image1_alt',
        'image1_title',
        'image2_alt',
        'image2_title',
        'image_left_alt',
        'image_left_title',
        'image_right_alt',
        'image_right_title',
        'label',
        'link',
        'link_url',
        'link_label',
        'link_secondary_label',
        'link2_url',
        'link2_label',
        'meta_description',
        'meta_keywords',
        'meta_title',
        'slug',
        'subtitle',
        'tag',
        'title',
        'title1',
        'title2',
        'title_secondary',
        'titleStep',
    ];

    protected LoggerInterface $logger;
    protected string $type;

    protected function __construct(string $type, LoggerInterface $translationLogger)
    {
        $this->type = $type;
        $this->logger = $translationLogger;
    }

    protected function extractContentTranslations(array $content, array $extractedFields, bool $extractEmptyStrings = false): array
    {
        if (!is_array($content)) {
            return [];
        }

        $keysArrays = [];
        $blockCount = [];
        foreach ($content as $block) {
            if (!is_array($block) || !isset($block['code'], $block['data']) || !is_array($block['data'])) { // Invalid block
                continue;
            }

            if (!isset($blockCount[$block['code']])) {
                $blockCount[$block['code']] = 0;
            }

            $parentKey = sprintf('%s.%d', str_replace('.', '_', $block['code']), $blockCount[$block['code']]);
            $keysArrays[] = $this->extractContentTranslationsRecursively($parentKey, $block['data'], $extractedFields, $extractEmptyStrings);

            ++$blockCount[$block['code']]; // Increment block usage count
        }

        return array_merge(...$keysArrays); // Flatten array
    }

    protected function applyContentTranslations(array &$content, array $translationKeys, array $extractedFields): void
    {
        $parentKeyRegex = '/^(\S+)\.(\S+)\.(\S+)$/U';
        foreach ($translationKeys as $key => $value) {
            if (preg_match($parentKeyRegex, $key, $matches)) {
                [, $blockCode, $blockCount, $field] = $matches;

                $blockKey = sprintf('%s.%d', $blockCode, $blockCount);
                $blockIndex = $this->findBlockInContent($content, $blockCode, (int)$blockCount);

                if (null === $blockIndex || !isset($content[$blockIndex]['data'])) {
                    $this->logger->warning(sprintf('[%s] Translation block key "%s" not found', strtoupper($this->type), $blockKey));
                    continue;
                }

                $this->applyContentTranslationToBlockRecursively($blockKey, $content[$blockIndex]['data'], $field, $extractedFields, $value);
            }
        }
    }

    protected function addPrefixCodeToKeys(array $array, string $code): array
    {
        $code = Slugifier::slugifyCode($code);
        $result = [];
        foreach ($array as $key => $value) {
            $result[$code . '.' . $key] = $value;
        }
        return $result;
    }

    protected function removeCodeFromTranslatableKeys(array $array, string $code): array
    {
        $code = Slugifier::slugifyCode($code);
        $result = [];
        foreach ($array as $key => $value) {
            $result[str_replace($code . '.', '', $key)] = $value;
        }
        return $result;
    }

    private function extractContentTranslationsRecursively(string $parentKey, array $content, array $extractedFields, bool $extractEmptyStrings = false): array
    {
        $keys = [];
        $keysArrays = [];
        foreach ($content as $field => $value) {
            if (is_array($value)) {
                $keysArrays[] = array_merge($keys, $this->extractContentTranslationsRecursively(sprintf('%s.%s', $parentKey, $field), $value, $extractedFields, $extractEmptyStrings));
                continue;
            }

            if (!is_string($value) || !in_array($field, $extractedFields, true)) {
                continue;
            }

            if (!$extractEmptyStrings && empty($value)) {
                continue;
            }

            $itemKey = sprintf('%s.%s', $parentKey, $field);
            $keys[$itemKey] = $value;
        }
        return array_merge($keys, ...$keysArrays); // Flatten array
    }

    private function applyContentTranslationToBlockRecursively(string $parentKey, array &$content, string $field, array $extractedFields, string $value): void
    {
        if (empty($field)) {
            return;
        }

        if (isset($content[$field]) && is_string($content[$field]) && in_array($field, $extractedFields, true)) { // Direct access to field
            $this->logger->debug(sprintf('[%s] Updating translation for key %s.%s', strtoupper($this->type), $parentKey, $field), ['value' => $value]);
            $content[$field] = $value;
            return;
        }

        // Need to recursively search for the field in case of array
        $fieldPath = explode('.', $field);
        $fieldIndex = array_shift($fieldPath);
        $childField = implode('.', $fieldPath); // Path of the child field
        $newParentKey = sprintf('%s.%s', $parentKey, $fieldIndex);

        // Check if array index exists
        if (null === $fieldIndex || !isset($content[$fieldIndex])) { /** @phpstan-ignore-line */
            $this->logger->warning(sprintf('[%s] Translation field key "%s" not found', strtoupper($this->type), $parentKey . '.' . $field));
            return;
        }

        $this->applyContentTranslationToBlockRecursively($newParentKey, $content[$fieldIndex], $childField, $extractedFields, $value);
    }

    private function findBlockInContent(array $content, string $blockCode, int $blockCount): ?int
    {
        $count = 0;
        foreach ($content as $index => $block) {
            if (isset($block['code']) && str_replace('.', '_', $block['code']) === $blockCode) {
                if ($count === $blockCount) {
                    return $index; // Return the right block count
                }
                $count++;
            }
        }
        return null;
    }
}
