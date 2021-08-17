<?php

namespace App\Repository;

use App\Entity\AccountSessionHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountSessionHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountSessionHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountSessionHistory[]    findAll()
 * @method AccountSessionHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountSessionHistoryRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountSessionHistory::class);
    }

    // /**
    //  * @return AccountSessionHistory[] Returns an array of AccountSessionHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccountSessionHistory
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
