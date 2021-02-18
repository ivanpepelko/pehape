<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        ArticleRepository $articleRepository
    ): Response {
        return $this->render(
            'default/index.html.twig',
            [
                'articles' => $articleRepository->getIndexArticles(0),
            ]
        );
    }

    #[Route('/article/{id}', name: 'article_show', methods: ['GET'])]
    public function show(
        Article $article
    ): Response {
        return $this->render(
            'article/show.html.twig',
            [
                'article' => $article,
            ]
        );
    }
}
