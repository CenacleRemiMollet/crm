<?php

namespace App\Repository;

use App\Entity\UserClubSubscribe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserClubSubscribe|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserClubSubscribe|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserClubSubscribe[]    findAll()
 * @method UserClubSubscribe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserClubSubscribeRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserClubSubscribe::class);
    }

    // /**
    //  * @return UserClubSubscribe[] Returns an array of UserClubSubscribe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserClubSubscribe
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
