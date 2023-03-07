<?php

declare(strict_types=1);

namespace App\Model\UiElement;

use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementInterface;
use MonsieurBiz\SyliusRichEditorPlugin\UiElement\UiElementTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class ProjectPlanUIModel implements UiElementInterface
{
    use UiElementTrait;

    private Filesystem $fileSystem;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        Filesystem $fileSystem,
        ParameterBagInterface $parameterBag
    ) {
        $this->fileSystem = $fileSystem;
        $this->parameterBag = $parameterBag;
    }

    // perform a little check on project plan file existence
    public function planFileExists(?string $filePath): bool
    {
        if (!is_string($filePath)) {
            return false;
        }
        
        $parameterName = $this->parameterBag->get('upload_dir_cms');
        \assert(is_string($parameterName));
        
        return $this->fileSystem->exists($parameterName . $filePath);
    }
}
