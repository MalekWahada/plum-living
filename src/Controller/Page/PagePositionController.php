<?php

declare(strict_types=1);

namespace App\Controller\Page;

use App\Entity\Page\Page;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class PagePositionController
{
    private CsrfTokenManagerInterface $csrfTokenManager;
    private EntityManagerInterface $entityManager;
    private FlashBagInterface $flashBag;

    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        FlashBagInterface $flashBag
    ) {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->entityManager = $entityManager;
        $this->flashBag = $flashBag;
    }

    public function updatePagesPositions(Request $request): Response
    {
        $csrfToken = new CsrfToken('update-pages-positions', $request->get('_csrf_token'));
        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }

        /** @var array|null $pagesPositions */
        $pagesPositions = $request->get('pages');
        $referer = $request->headers->get('referer');

        if (is_array($pagesPositions)) {
            foreach ($pagesPositions as $id => $position) {
                try {
                    $this->updatePagePosition($id, $position);
                } catch (InvalidArgumentException $exception) {
                    $this->flashBag->add('error', $exception->getMessage());

                    return new RedirectResponse($referer);
                }
            }

            $this->entityManager->flush();
            $this->flashBag->add('success', 'app.cms.page_position.success');
        }

        return new RedirectResponse($referer);
    }

    private function updatePagePosition(int $id, string $position): void
    {
        /** @var Page|null $page */
        $page = $this->entityManager->find(Page::class, $id);

        if (null === $page) {
            return;
        }
        $page->setPosition((int)$position);
        $this->entityManager->persist($page);
    }
}
