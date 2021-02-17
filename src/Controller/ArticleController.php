<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
#[IsGranted('ROLE_ADMIN')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'article_index', methods: ['GET'])]
    public function index(
        ArticleRepository $articleRepository
    ): Response {
        return $this->render(
            'article/index.html.twig',
            [
                'articles' => $articleRepository->findAll(),
            ]
        );
    }

    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setOwner($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render(
            'article/new.html.twig',
            [
                'article' => $article,
                'form'    => $form->createView(),
            ]
        );
    }

    #[Route('/{id}', name: 'article_show', methods: ['GET'])]
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

    #[Route('/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Article $article
    ): Response {
        if (!$this->isGranted('ROLE_SUPERADMIN') && $this->getUser()->getUsername() !== $article->getOwner()->getUsername()) {
            $this->addFlash('user_error', 'Nije dozvoljena promjena traženog objekta.');

            return $this->redirectToRoute('article_index');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render(
            'article/edit.html.twig',
            [
                'article' => $article,
                'form'    => $form->createView(),
            ]
        );
    }

    #[Route('/{id}', name: 'article_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Article $article
    ): Response {
        if (!$this->isGranted('ROLE_SUPERADMIN') && $this->getUser()->getUsername() !== $article->getOwner()->getUsername()) {
            $this->addFlash('user_error', 'Nije dozvoljeno brisanje traženog objekta.');

            return $this->redirectToRoute('article_index');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }
}
