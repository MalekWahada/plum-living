<?php

declare(strict_types=1);

namespace App\Controller\PlumStyler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StylerController extends AbstractController
{
    public function indexAction(Request $request): Response
    {
        return $this->render('Shop/PlumStyler/index.html.twig');
    }

    public function showAction(Request $request, SessionInterface $session): Response
    {
        $sessionId = $session->get('styler_session_id');
        if (null === $sessionId) {
            $sessionId = uniqid('styler_', true);
            $session->set('styler_session_id', $sessionId);
        }
        $sceneId = $request->query->get('sceneId', '7');

        return $this->render('Shop/PlumStyler/show.html.twig', [
            'sceneId' => $sceneId,
            'sessionId' => $sessionId,
        ]);
    }
}
