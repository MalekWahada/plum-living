<?php

declare(strict_types=1);

namespace App\Provider\CMS\PageContent;

use App\Calculator\CMS\LectureTimeCalculator;
use App\Calculator\CMS\ProjectTotalCalculator;
use App\Entity\Page\Page;
use MonsieurBiz\SyliusCmsPagePlugin\Repository\PageRepository;

class PageContentProvider
{
    private ProjectTotalCalculator $projectTotalCalculator;
    private LectureTimeCalculator $lectureTimeCalculator;
    private PageRepository $pageRepository;

    public function __construct(
        ProjectTotalCalculator $projectTotalCalculator,
        LectureTimeCalculator $lectureTimeCalculator,
        PageRepository $pageRepository
    ) {
        $this->projectTotalCalculator = $projectTotalCalculator;
        $this->lectureTimeCalculator = $lectureTimeCalculator;
        $this->pageRepository = $pageRepository;
    }

    public function calculateTotal(?array $cardContent): ?float
    {
        if ($cardContent === null) {
            return 0;
        }

        return $this->projectTotalCalculator->calculateTotal($cardContent);
    }

    public function getLectureTime(?array $content): ?int
    {
        if ($content === null) {
            return 0;
        }

        return $this->lectureTimeCalculator->calculateLectureTime($content);
    }

    public function getSlug(string $code): ?string
    {
        $page = $this->pageRepository->findOneBy([
            'code' => $code,
            'enabled' => true,
        ]);
        if ($page instanceof Page) {
            return $page->getSlug();
        }
        return null;
    }
}
