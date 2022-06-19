<?php

namespace App\Repository;

use App\Entity\ClubPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<ClubPrice>
 *
 * @method ClubPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClubPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClubPrice[]    findAll()
 * @method ClubPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClubPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubPrice::class);
    }

    public function add(ClubPrice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClubPrice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
//     public function findByClubIds($clubIds) {
//         $sql = "SELECT cp.club_id AS club_id, loc.*"
//             ." FROM club_price cp"
//             ."  JOIN club_location loc ON les.club_location_id = loc.id"
//             ." WHERE les.club_id IN (:clubIds)"
//             ." GROUP BY 1, 2";
//         $rsm = new ResultSetMappingBuilder($this->getEntityManager());
//         $rsm->addRootEntityFromClassMetadata('App\Entity\ClubLocation', 'l');
//         $rsm->addScalarResult('club_id', 'c');
//         $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
//         $query->setParameter('clubIds', $clubIds);
//         return $query->getResult();
//     }
    
    
//    /**
//     * @return ClubPrice[] Returns an array of ClubPrice objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ClubPrice
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
