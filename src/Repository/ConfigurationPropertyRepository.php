<?php

namespace App\Repository;

use App\Entity\ConfigurationProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConfigurationProperty|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigurationProperty|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigurationProperty[]    findAll()
 * @method ConfigurationProperty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationPropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigurationProperty::class);
    }

    /**
     * @return ConfigurationProperty[] Returns an array of ConfigurationProperty objects
     */
    public function findByStartsWith($keyPrefix)
    {
    	return $this->createQueryBuilder('c')
            ->andWhere('c.property_key LIKE :keyPrefix')
            ->setParameter('keyPrefix', $keyPrefix.'%')
            //->orderBy('c.id', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ConfigurationProperty[] Returns an array of ConfigurationProperty objects
     */
    public function findByKeys($keys)
    {
    	return $this->createQueryBuilder('c')
    	->andWhere('c.property_key IN (:keys)')
    	->setParameter('keys', $keys)
    	//->orderBy('c.id', 'ASC')
    	//->setMaxResults(10)
    	->getQuery()
    	->getResult()
    	;
    }

    /*
    public function findOneBySomeField($value): ?ConfigurationProperty
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
