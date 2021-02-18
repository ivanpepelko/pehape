<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return iterable<Article>
     */
    public function getIndexArticles(int $offset, int $limit = 10): iterable
    {
        $qb = $this->createQueryBuilder('a');

        return $qb->where($qb->expr()->eq('a.draft', ':draft'))
                  ->setParameter('draft', false)
                  ->andWhere($qb->expr()->gte('a.releaseDate', ':release'))
                  ->setParameter('release', 'CURRENT_TIMESTAMP()')
                  ->setMaxResults($limit)
                  ->setFirstResult($offset)
                  ->getQuery()
                  ->getResult();
    }
}
