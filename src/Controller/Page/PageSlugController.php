<?php

declare(strict_types=1);

namespace App\Controller\Page;

use App\Entity\Page\Page;
use MonsieurBiz\SyliusCmsPagePlugin\Controller\Admin\Page\PageSlugController as BasePageSlugController;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PageSlugController extends BasePageSlugController
{
    private SlugGeneratorInterface $slugGenerator;

    public function __construct(SlugGeneratorInterface $slugGenerator)
    {
        parent::__construct($slugGenerator);
        $this->slugGenerator = $slugGenerator;
    }

    public function generateAction(Request $request): JsonResponse
    {
        $type = (string) $request->query->get('type');
        $name = (string) $request->query->get('title');

        if ($type === Page::PAGE_TYPE_MEDIA_ARTICLE) {
            $slug = $type . '/' . $this->slugGenerator->generate($name);

            return new JsonResponse([
                'slug' => $slug,
            ]);
        }

        return parent::generateAction($request);
    }
}
