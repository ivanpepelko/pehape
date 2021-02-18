<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/question')]
#[IsGranted('ROLE_USER')]
class QuestionController extends AbstractController
{
    #[Route('/', name: 'question_index', methods: ['GET'])]
    public function index(
        QuestionRepository $questionRepository
    ): Response {
        $questions = $questionRepository->findAll();

        return $this->render('question/index.html.twig', ['questions' => $questions]);
    }

    #[Route('/new', name: 'question_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setUser($this->getUser())
                     ->setDateTime(new DateTime());
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/new.html.twig',
            [
                'question' => $question,
                'form'     => $form->createView(),
            ]
        );
    }

    #[Route('/{id}', name: 'question_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        Question $question,
        AnswerRepository $answerRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $answer = new Answer();
        $answer->setUser($this->getUser());
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->addAnswer($answer->setDateTime(new DateTime()));
            $entityManager->flush();
            $entityManager->refresh($question);
        }

        $answers = $answerRepository->getLatest($question);
        $entityManager->clear(Answer::class);

        return $this->render(
            'question/show.html.twig',
            [
                'question' => $question,
                'answers'  => $answers,
                'form'     => $form->createView(),
            ]
        );
    }

    #[Route('/{id}/edit', name: 'question_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Question $question,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted('ROLE_SUPERADMIN') && $this->getUser()->getUsername() !== $question->getUser()->getUsername()) {
            $this->addFlash('user_error', 'Nije dozvoljena izmjena upita.');

            return $this->redirectToRoute('question_index');
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('question_index');
        }

        return $this->render(
            'question/edit.html.twig',
            [
                'question' => $question,
                'form'     => $form->createView(),
            ]
        );
    }

    #[Route('/{id}', name: 'question_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Question $question,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted('ROLE_SUPERADMIN') && $this->getUser()->getUsername() !== $question->getUser()->getUsername()) {
            $this->addFlash('user_error', 'Nije dozvoljeno brisanje upita.');

            return $this->redirectToRoute('question_index');
        }

        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
        }

        return $this->redirectToRoute('question_index');
    }
}
