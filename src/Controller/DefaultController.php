<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
    ): Response {
        $totalCount = $articleRepository->getTotalCount();

        $offset = $request->query->getInt('offset');
        if ($offset < 0) {
            $offset = 0;
        }

        if ($offset > $totalCount) {
            $offset = intdiv($totalCount, 10) * 10;
        }

        if ($offset % 10 !== 0) {
            $offset = intdiv($offset, 10) * 10;
        }

        return $this->render(
            'default/index.html.twig',
            [
                'articles' => $articleRepository->getIndexArticles($offset),
                'count'    => $totalCount,
                'offset'   => $offset,
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

    #[Route('/search', name: 'search')]
    public function search(
        Request $request,
        SearchService $search
    ): Response {
        return $this->render(
            'default/search.html.twig',
            [
                'search_results' => $search->getResults(strtolower(trim($request->query->get('q')))),
            ]
        );
    }
}
