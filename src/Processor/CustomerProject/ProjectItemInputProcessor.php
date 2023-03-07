<?php

declare(strict_types=1);

namespace App\Processor\CustomerProject;

use App\Entity\CustomerProject\ProjectItem;
use App\Entity\CustomerProject\ProjectItemVariant;
use App\Entity\Product\ProductOption;
use App\Model\CustomerProject\ProjectItemOptionsPayloadModel;
use App\Repository\Product\ProductOptionValueRepository;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

class ProjectItemInputProcessor
{
    private const OPTION_VARIANT_CODE = 'variant';

    private ProjectItem $item;
    private ProjectItemOptionsPayloadModel $payload;
    private array $validOptions = [];
    private array $optionsValues = [];
    private ?ProjectItemVariant $matchedVariant = null;

    /**
     * @var array|string[]
     */
    private array $availableOptions;
    private ?ProductOptionValueInterface $designOptionQuery = null;
    private ?ProductOptionValueInterface $finishOptionQuery = null;
    private ?ProductOptionValueInterface $colorOptionQuery = null;
    private ?ProductOptionValueInterface $handleFinishOptionQuery = null;
    private ?ProductOptionValueInterface $tapFinishOptionQuery = null;

    public function __construct(ProjectItem $item, ProjectItemOptionsPayloadModel $payload)
    {
        $this->item = $item;
        $this->payload = $payload;

        $this->availableOptions = $item->getVariantsOptionsCodesFiltered(); // Get available options
    }

    /**
     * @return array|string[]
     */
    public function getAvailableOptions(): array
    {
        return $this->availableOptions;
    }

    /**
     * @return array|bool[]
     */
    public function getValidOptions(): array
    {
        return $this->validOptions;
    }

    public function getOptionsValues(): array
    {
        return array_filter($this->optionsValues, static function ($value) {
            return !empty($value);
        });
    }

    public function hasNoOptionsAvailable(): bool
    {
        return empty($this->availableOptions);
    }

    public function hasOption(string $optionCode): bool
    {
        return in_array($optionCode, $this->availableOptions, true);
    }

    public function getDesignOptionQuery(): ?ProductOptionValueInterface
    {
        return $this->designOptionQuery;
    }

    public function getFinishOptionQuery(): ?ProductOptionValueInterface
    {
        return $this->finishOptionQuery;
    }

    public function getColorOptionQuery(): ?ProductOptionValueInterface
    {
        return $this->colorOptionQuery;
    }

    public function getHandleFinishOptionQuery(): ?ProductOptionValueInterface
    {
        return $this->handleFinishOptionQuery;
    }

    public function getMatchedVariant(): ?ProjectItemVariant
    {
        return $this->matchedVariant;
    }

    public function isVariantMatchedValid(): bool
    {
        // Check if all options available are valid
        foreach (ProjectItem::AVAILABLE_OPTION_CODES as $optionCode) {
            if (in_array($optionCode, $this->availableOptions, true) && !$this->isValidOption($optionCode)) {
                return false;
            }
        }

        // If no options are available, then check variant else return true
        return !$this->hasNoOptionsAvailable() || $this->isValidOption(self::OPTION_VARIANT_CODE);
    }

    /**
     * @return array|ProjectItemVariant[]
     */
    public function getVariantsAvailable(): array
    {
        return $this->optionsValues['variants'];
    }

    /**
     * @throws NonUniqueResultException
     */
    public function process(ProductOptionValueRepository $productOptionValueRepository, bool $tryToAutoFill = false, bool $strictMatching = true): void
    {
        // Use VARIANT for option of no other options are available
        if ($this->hasNoOptionsAvailable()) {
            $variants = $this->item->getVariants()->filter(function (ProjectItemVariant $variant) {
                return null !== $variant->getProductVariant();
            });
            $matchedVariant = $this->payload->getVariant() !== null ? $variants->filter(function (ProjectItemVariant $itemVariant) {
                return $itemVariant->getProductVariant()->getId() === (int)$this->payload->getVariant(); // Project item id could be null on new item, so we are comparing with variant id
            })->first() : false; // Return false if not found
            $this->matchedVariant = $matchedVariant !== false ? $matchedVariant : null;

            // Try to autofill variant when only one is available
            if (null === $this->matchedVariant && $tryToAutoFill && count($variants) === 1) {
                $this->matchedVariant = $variants[0];
            }

            $this->setValidOption(self::OPTION_VARIANT_CODE, $this->matchedVariant !== null);

            // Generate options list for variants
            $this->optionsValues['variants'] = $variants->toArray();
            return;
        }

        // Options are available: Get queries from payload
        $this->designOptionQuery = $this->processQueryOption(ProductOption::PRODUCT_OPTION_DESIGN, $productOptionValueRepository);
        $this->finishOptionQuery = $this->processQueryOption(ProductOption::PRODUCT_OPTION_FINISH, $productOptionValueRepository);
        $this->colorOptionQuery = $this->processQueryOption(ProductOption::PRODUCT_OPTION_COLOR, $productOptionValueRepository);
        $this->handleFinishOptionQuery = $this->processQueryOption(ProductOption::PRODUCT_HANDLE_OPTION_FINISH, $productOptionValueRepository);
        $this->tapFinishOptionQuery = $this->processQueryOption(ProductOption::PRODUCT_TAP_OPTION_FINISH, $productOptionValueRepository);

        $this->matchedVariant = $this->item->getVariantByOptionValues($this->designOptionQuery, $this->finishOptionQuery, $this->colorOptionQuery, $this->handleFinishOptionQuery, $this->tapFinishOptionQuery, $strictMatching);

        $autoFilled = false;
        $designs = $finishes = $colors = $handleFinishes = $tapFinishes = [];

        // Design
        if ($this->hasOption(ProductOption::PRODUCT_OPTION_DESIGN)) {
            $designs = $this->item->getAvailableDesigns();

            // Try to autofill finish when only one option is available
            if ($tryToAutoFill && count($designs) === 1) {
                $autoFilled = true;
                $this->designOptionQuery = $designs[0];
            }

            $this->setValidOption(ProductOption::PRODUCT_OPTION_DESIGN, null !== $this->designOptionQuery && $this->item->hasVariantsWithOptionValues($this->designOptionQuery));
        }

        //Finish
        if ($this->hasOption(ProductOption::PRODUCT_OPTION_FINISH)) {
            $finishes = $this->item->getAvailableFinishes($this->designOptionQuery);

            // Try to autofill finish when only one option is available
            if ($tryToAutoFill && count($finishes) === 1) {
                $autoFilled = true;
                $this->finishOptionQuery = $finishes[0];
            }

            $this->setValidOption(ProductOption::PRODUCT_OPTION_FINISH, null !== $this->finishOptionQuery && $this->item->hasVariantsWithOptionValues($this->designOptionQuery, $this->finishOptionQuery));
        }

        // Color
        if ($this->hasOption(ProductOption::PRODUCT_OPTION_COLOR)) {
            $colors = $this->item->getAvailableColors($this->designOptionQuery, $this->finishOptionQuery);

            // Try to autofill color when only one option is available
            if ($tryToAutoFill && count($colors) === 1) {
                $autoFilled = true;
                $this->colorOptionQuery = $colors[0];
            }

            $this->setValidOption(ProductOption::PRODUCT_OPTION_COLOR, null !== $this->colorOptionQuery && $this->item->hasVariantsWithOptionValues($this->designOptionQuery, $this->finishOptionQuery, $this->colorOptionQuery));
        }

        // Handle Finish
        if ($this->hasOption(ProductOption::PRODUCT_HANDLE_OPTION_FINISH)) {
            $handleFinishes = $this->item->getAvailableHandleFinishes();

            // Try to autofill when only one option is available
            if ($tryToAutoFill && count($handleFinishes) === 1) {
                $autoFilled = true;
                $this->handleFinishOptionQuery = $handleFinishes[0];
            }

            $this->setValidOption(ProductOption::PRODUCT_HANDLE_OPTION_FINISH, null !== $this->handleFinishOptionQuery && $this->item->hasVariantsWithOptionValues($this->designOptionQuery, $this->finishOptionQuery, $this->colorOptionQuery, $this->handleFinishOptionQuery));
        }

        // Tap Finish
        if ($this->hasOption(ProductOption::PRODUCT_TAP_OPTION_FINISH)) {
            $tapFinishes = $this->item->getAvailableTapFinishes();

            // Try to autofill when only one option is available
            if ($tryToAutoFill && count($tapFinishes) === 1) {
                $autoFilled = true;
                $this->tapFinishOptionQuery = $tapFinishes[0];
            }

            $this->setValidOption(ProductOption::PRODUCT_TAP_OPTION_FINISH, null !== $this->tapFinishOptionQuery && $this->item->hasVariantsWithOptionValues($this->designOptionQuery, $this->finishOptionQuery, $this->colorOptionQuery, $this->handleFinishOptionQuery, $this->tapFinishOptionQuery));
        }

        // Get the new variant that have been autofilled
        if ($tryToAutoFill && $autoFilled) {
            $this->matchedVariant = $this->item->getVariantByOptionValues($this->designOptionQuery, $this->finishOptionQuery, $this->colorOptionQuery, $this->handleFinishOptionQuery, $this->tapFinishOptionQuery, $strictMatching);
        }

        $this->optionsValues['designs'] = $designs;
        $this->optionsValues['finishes'] = $finishes;
        $this->optionsValues['colors'] = $colors;
        $this->optionsValues['handleFinishes'] = $handleFinishes;
        $this->optionsValues['tapFinishes'] = $tapFinishes;
    }

    private function setValidOption(string $optionCode, bool $isValid): void
    {
        $this->validOptions[$optionCode] = $isValid;
    }

    private function isValidOption(string $optionCode): bool
    {
        return isset($this->validOptions[$optionCode]) && $this->validOptions[$optionCode];
    }

    /**
     * @throws NonUniqueResultException
     */
    private function processQueryOption(string $optionCode, ProductOptionValueRepository $productOptionValueRepository): ?ProductOptionValueInterface
    {
        if (!$this->hasOption($optionCode) || null === $this->payload->getOption($optionCode)) {
            return null;
        }

        return $productOptionValueRepository->finOneByCodeAndOptionCode((string)$this->payload->getOption($optionCode), $optionCode);
    }
}
