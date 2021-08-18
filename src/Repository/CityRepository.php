<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, City::class);
	}

	public function findByStartsWith($value, $limit = 10)
	{
		return $this->createQueryBuilder('c')
		    ->andWhere('c.city_name LIKE :val OR c.zip_code LIKE :val')
		    ->setParameter('val', $value.'%')
		    ->orderBy('c.city_name, c.zip_code', 'ASC')
		    ->setMaxResults(max(min($limit, 50), 1))
		    ->getQuery()
		    ->getResult()
		;
	}

}
