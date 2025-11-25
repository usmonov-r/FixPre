<?php

namespace App\Repository;

use App\Entity\FeedbackResult;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedbackResult>
 */
class FeedbackResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackResult::class);
    }

    public function findUserStats(User $user): array
    {
        return $this->createQueryBuilder('f') // 'f' == FeedbackResult
        ->select('AVG(f.overallScore) as averageScore, COUNT(f.id) as totalPresentations')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleResult();
    }

    public function findAverageStats(): array
    {
        return $this->createQueryBuilder('f')
            ->select('AVG(f.overallScore) as platformAverage')
            ->where('f.overallScore IS NOT NULL')
            ->getQuery()
            ->getSingleResult();
    }

    //    /**
    //     * @return FeedbackResult[] Returns an array of FeedbackResult objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FeedbackResult
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
