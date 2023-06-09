<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
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

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updateClause(Article $article, $dateAdded, $note)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query = $queryBuilder->update($article, 'u')
            ->set('u.note', ':note')
            ->set('u.dateAdded', ':dateAdded')
            ->setParameter('note', $note)
            ->setParameter('dateAdded', $dateAdded)
            ->getQuery();
        $result = $query->execute();
        return $result;
    }

    public function findOneByTitle($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.title = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
