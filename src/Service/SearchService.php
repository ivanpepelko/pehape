<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Article;
use App\Entity\Gallery;
use App\Entity\Question;
use App\Model\SearchResults;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
class SearchService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
    }

    public function getResults(string $term): SearchResults
    {
        $results = new SearchResults($term);

        $results->articles = $this->searchArticles($term);
        $results->questions = $this->searchQuestions($term);
        $results->answers = $this->searchAnswers($term);
        $results->galleries = $this->searchGalleries($term);

        return $results;
    }

    /**
     * @param string $term
     *
     * @return iterable
     */
    private function searchArticles(string $term): iterable
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('a')
                  ->from(Article::class, 'a')
                  ->where(
                      $qb->expr()->orX(
                          $qb->expr()->like('a.title', ':term'),
                          $qb->expr()->like('a.description', ':term'),
                          $qb->expr()->like('a.body', ':term'),
                      )
                  )
                  ->setParameter('term', "%$term%")
                  ->andWhere($qb->expr()->eq('a.draft', 0))
                  ->andWhere($qb->expr()->gte('a.releaseDate', 'CURRENT_TIMESTAMP()'))
                  ->getQuery()
                  ->getResult();
    }

    private function searchQuestions(string $term): iterable
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('q')
                  ->from(Question::class, 'q')
                  ->where(
                      $qb->expr()->orX(
                          $qb->expr()->like('q.subject', ':term'),
                          $qb->expr()->like('q.body', ':term'),
                      )
                  )
                  ->setParameter('term', "%$term%")
                  ->getQuery()
                  ->getResult();
    }

    private function searchAnswers(string $term): iterable
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('a')
                  ->from(Answer::class, 'a')
                  ->where($qb->expr()->like('a.body', ':term'))
                  ->setParameter('term', "%$term%")
                  ->getQuery()
                  ->getResult();
    }

    private function searchGalleries(string $term): iterable
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('g')
                  ->from(Gallery::class, 'g')
                  ->where($qb->expr()->like('g.title', ':term'))
                  ->setParameter('term', "%$term%")
                  ->getQuery()
                  ->getResult();
    }

}
