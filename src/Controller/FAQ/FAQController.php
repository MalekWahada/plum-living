<?php

declare(strict_types=1);

namespace App\Controller\FAQ;

use App\Wrapper\Zendesk\ZendeskWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class FAQController
{
    private ZendeskWrapper $zendeskWrapper;
    private Environment $twig;

    public function __construct(
        Environment $twig,
        ZendeskWrapper $zendeskWrapper
    ) {
        $this->zendeskWrapper = $zendeskWrapper;
        $this->twig = $twig;
    }

    public function index(Request $request): Response
    {
        return new Response(
            $this->twig->render(
                'Shop/Plum/FAQ/Category/index.html.twig',
                [
                    'categories' => $this->zendeskWrapper->getAllFAQ($request->getLocale()),
                ]
            )
        );
    }

    public function getArticle(Request $request, int $articleId): Response
    {
        $article = $this->zendeskWrapper->getArticle($articleId, $request->getLocale());
        if (empty($article)) {
            return new Response(
                $this->twig->render('Shop/Plum/FAQ/Article/_article_not_found.html.twig'),
                404
            );
        }

        return new Response(
            $this->twig->render(
                'Shop/Plum/FAQ/Article/_single_article.html.twig',
                [
                    'article' => $article,
                ]
            )
        );
    }
}
