<?php

declare(strict_types=1);

namespace App\EventListener;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use function end;
use function is_array;
use function sprintf;

final class AdminExportGridListener
{
    private ?RequestStack $requestStack = null;

    private array $formats;

    public function __construct(array $formats)
    {
        $this->formats = $formats;
    }

    public function setRequest(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function onProductVariantGrid(GridDefinitionConverterEvent $event): void
    {
        $event->stopPropagation();
    }

    public function onProductGrid(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');

        if (!$actionGroup->hasAction('export-products')) {
            $action = Action::fromNameAndType('export-product', 'links');
            $action->setLabel('app.ui.import_export.export_products');
            $action->setOptions($this->getLinkOptions('sylius.product'));
            $actionGroup->addAction($action);
        }

        if (!$actionGroup->hasAction('export-product-variants')) {
            $action = Action::fromNameAndType('export-product-variants', 'links');
            $action->setLabel('app.ui.import_export.export_product_variants');
            $action->setOptions($this->getLinkOptions('sylius.product_variant'));
            $actionGroup->addAction($action);
        }

        $event->stopPropagation();
    }

    public function onCombinationGrid(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');

        if (!$actionGroup->hasAction('export-combinations')) {
            $action = Action::fromNameAndType('export-combinations', 'links');
            $action->setLabel('app.ui.import_export.export_combinations');
            $action->setOptions($this->getLinkOptions('app.combination'));
            $actionGroup->addAction($action);
        }

        $event->stopPropagation();
    }

    public function onProductGroupGrid(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');

        if (!$actionGroup->hasAction('export-product-groups')) {
            $action = Action::fromNameAndType('export-product-groups', 'links');
            $action->setLabel('app.ui.import_export.export_product_groups');
            $action->setOptions($this->getLinkOptions('app.product_group'));
            $actionGroup->addAction($action);
        }

        $event->stopPropagation();
    }

    private function getLinkOptions(string $resource): array
    {
        return [
            'class' => '',
            'icon' => 'save',
            'header' => [
                'icon' => 'file code outline',
                'label' => 'sylius.ui.type',
            ],
            'links' => $this->createLinks($resource),
        ];
    }

    /**
     * @param string $resource
     * @return array[]
     */
    private function createLinks(string $resource): array
    {
        $links = [];
        foreach ($this->formats as $format) {
            $links[] = $this->addLink($resource, $format);
        }

        return $links;
    }

    private function addLink(string $resource, string $format): array
    {
        $parameters = [
            'resource' => $resource,
            'format' => $format,
        ];

        if (null !== $this->requestStack && null !== ($currentRequest = $this->requestStack->getCurrentRequest())) {
            // @TODO Find way to validate the list of criteria injected
            $parameters['criteria'] = $currentRequest->query->get('criteria');
        }

        $explode = explode('.', $resource);
        if (is_array($explode) && !empty($explode)) {
            $resource = end($explode);
        }

        return [
            'label' => 'fos.import_export.ui.types.' . $format,
            'icon' => 'file archive',
            'route' => sprintf('app_export_data_%s', $resource),
            'parameters' => $parameters,
        ];
    }
}
