<?php

declare(strict_types=1);

namespace App\Controller\Conception;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConceptionController extends AbstractController
{
    private const CONCEPTION_TYPEFORM_URLS = [
        'fr' => 'https://plumkitchen.typeform.com/to/raAFovpl',
        'en' => 'https://plumkitchen.typeform.com/conceptionEN',
    ];

    public function __invoke(Request $request): Response
    {
        $formUrl = self::CONCEPTION_TYPEFORM_URLS[$request->getLocale()] ?? self::CONCEPTION_TYPEFORM_URLS['en']; // English is default
        return $this->render('Shop/Plum/Conception/form.html.twig', ['formUrl' => $formUrl]);
    }

    public function completedAction(): Response
    {
        return $this->render('Shop/Plum/Conception/completed.html.twig');
    }
}
