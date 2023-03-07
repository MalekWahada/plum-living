<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait EshopControllerTrait
{
    /**
     * @param array $options Selected options (facade type, design , finish or color)
     * @param string $eshopTemplate Main template
     * @param string $optionsTemplate Template represent the selected options (design , finish or color)
     * @param bool $isAjaxRequest
     * @return Response
     */
    protected function returnEshopViews(
        array $options,
        string $eshopTemplate,
        string $optionsTemplate,
        bool $isAjaxRequest
    ): Response {
        $mainView = $this->renderView($optionsTemplate, $options);
        $sideBarMenu = $this->renderView(
            "Shop/Tunnel/Shopping/Partial/_sidebar.html.twig",
            [
                'facadeType' => $options['facadeType'],
                'design' => $options['design'] ?? null,
                'finish' => $options['finish'] ?? null,
                'color' => $options['color'] ?? null,
            ]
        );

        if ($isAjaxRequest) {
            $response = [
                'mainView' => $mainView,
                'sideBarMenu' => $sideBarMenu
            ];

            return new JsonResponse($response);
        }

        return $this->render($eshopTemplate, [
            'facadeType' => $options['facadeType'],
            'design' => $options['design'] ?? null,
            'finish' => $options['finish'] ?? null,
            'color' => $options['color'] ?? null,
            'sideBarMenu' => $sideBarMenu,
            'optionListView' => $mainView,
        ]);
    }
}
