<?php

declare(strict_types=1);

namespace App\Factory\Product;

use App\Entity\Product\ProductCompleteInfoTranslation;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Yaml\Yaml;

class ProductCompleteInfoTranslationFactory
{
    private const CONTENT_FILE_NAME = 'product_complete_info_content.yaml';

    private FactoryInterface $decoratedFactory;
    private string $templatesDirectory;

    public function __construct(
        FactoryInterface $decoratedFactory,
        string $templatesDirectory
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->templatesDirectory = $templatesDirectory;
    }

    public function createNew(): ProductCompleteInfoTranslation
    {
        /** @var ProductCompleteInfoTranslation $completeInfoTranslation */
        $completeInfoTranslation = $this->decoratedFactory->createNew();

        return $completeInfoTranslation;
    }

    public function createFromContent(): ProductCompleteInfoTranslation
    {
        $completeInfoTranslation = $this->createNew();

        $infoContent = Yaml::parseFile($this->templatesDirectory . self::CONTENT_FILE_NAME);
        $infoContent = json_encode($infoContent);
        $infoContent = false === $infoContent ? '' :  $infoContent;

        $completeInfoTranslation->setContent($infoContent);

        return $completeInfoTranslation;
    }
}
