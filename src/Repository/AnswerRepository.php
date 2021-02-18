<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Doctrine\ORM\QueryBuilder;

/**
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * @param Question $question
     * @param int      $limit
     *
     * @return iterable<Answer>
     */
    public function getLatest(Question $question, int $limit = 5): iterable
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a')
           ->where($qb->expr()->eq('a.question', ':question'))
           ->setParameter(':question', $question->getId())
           ->orderBy('a.dateTime', 'DESC')
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

}
