<?php

declare(strict_types=1);

namespace App\Export\Plugin;

use App\Entity\Tunnel\Shopping\Combination;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;

final class CombinationResourcePlugin extends ResourcePlugin
{
    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport): void
    {
        parent::init($idsToExport);

        /** @var Combination $resource */
        foreach ($this->resources as $resource) {
            $this->addData($resource);
            $this->addOptions($resource);
            $this->addImageData($resource);
        }
    }

    private function addData(Combination $resource): void
    {
        $this->addDataForResource($resource, 'Id', $resource->getId());
        if (null !== $resource->getFacadeType()) {
            $this->addDataForResource($resource, 'FacadeType', $resource->getFacadeType()->getCode());
        }
        $this->addDataForResource($resource, 'Recommendation', $resource->getRecommendation());
    }


    private function addImageData(Combination $resource): void
    {
        if (null !== $image = $resource->getImage()) {
            $this->addDataForResource(
                $resource,
                'Image',
                $image->getPath()
            );
        }
    }

    private function addOptions(Combination $resource): void
    {
        if (null !== $resource->getDesign()) {
            $this->addDataForResource($resource, 'Design', $resource->getDesign()->getCode());
        }
        if (null !== $resource->getFinish()) {
            $this->addDataForResource($resource, 'Finish', $resource->getFinish()->getCode());
        }
        if (null !== $resource->getColor()) {
            $this->addDataForResource($resource, 'Color', $resource->getColor()->getCode());
        }
    }
}
