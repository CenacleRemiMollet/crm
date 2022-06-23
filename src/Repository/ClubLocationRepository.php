<?php

namespace App\Repository;

use App\Entity\ClubLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use App\Model\ClubLocationView;
use App\Model\ClubView;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClubLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClubLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClubLocation[]	findAll()
 * @method ClubLocation[]	findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClubLocationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ClubLocation::class);
	}

	public function findByUuid($uuid) {
		// TODO
// 		$sql = "SELECT *"
// 			." FROM club_lesson les"
// 			."  JOIN club_location loc ON les.club_location_id = loc.id"
// 			." WHERE les.club_id IN (:clubIds)"
// 			." GROUP BY 1, 2";
// 		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
// 		$rsm->addRootEntityFromClassMetadata('App\Entity\ClubLocation', 'l');
// 		$rsm->addScalarResult('club_id', 'c');
// 		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
// 		$query->setParameter('clubIds', $clubIds);
// 		return $query->getResult();
	}


	public function findByClubIds($clubIds) {
		$sql = "SELECT les.club_id AS club_id, loc.*"
			." FROM club_lesson les"
			."  JOIN club_location loc ON les.club_location_id = loc.id"
			." WHERE les.club_id IN (:clubIds)"
			." GROUP BY 1, 2";
		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
		$rsm->addRootEntityFromClassMetadata('App\Entity\ClubLocation', 'l');
		$rsm->addScalarResult('club_id', 'c');
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter('clubIds', $clubIds);
		return $query->getResult();
	}

	public function findByZipcodeAndDistance($zipcode, $distance, $disciplines, $days, $active) {
		$sql = "SELECT cl.*"
				." FROM club_location cl"
		        ."  JOIN club_lesson cles ON (cles.club_location_id = cl.id)"
		        ."  JOIN club c ON (c.id = cles.club_id)";
	    if(! is_null($zipcode) && ! empty($zipcode) && ! is_null($distance)) {
	        $sql = 	$sql."  JOIN ("
	            ."   SELECT clist.zip_code,"
	            ."          (6371 * acos( cos(radians(cref.latitude)) * cos(radians(clist.latitude)) * cos(radians(clist.longitude) - radians(cref.longitude)) + sin(radians(cref.latitude)) * sin(radians(clist.latitude)))) AS distance"
	            ."    FROM city clist, (SELECT * FROM city WHERE zip_code = :zipcode LIMIT 1) cref"
	            ."    HAVING distance < :distance"
	            ."  ) c ON cl.zipcode = c.zip_code";
	    }
	    $sql = 	$sql." WHERE ";
	    if(! is_null($active) && ! $active) {
	        $sql = 	$sql." NOT ";
	    }
	    $sql = 	$sql." c.active";
		    
		if(! is_null($disciplines) && ! empty($disciplines)) {
		    $sql = 	$sql." AND cles.discipline IN ('".implode("','", $disciplines)."')";
		}
		if(! is_null($days) && ! empty($days)) {
		    $sql = 	$sql." AND cles.day_of_week IN ('".implode("','", $days)."')";
		}
						
		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
		$rsm->addRootEntityFromClassMetadata('App\Entity\ClubLocation', 'l');
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		if(! is_null($zipcode) && ! is_null($distance)) {
            $query->setParameter('zipcode', $zipcode);
            $query->setParameter('distance', $distance);
		}
		return $query->getResult();
	}



	// /**
	//  * @return ClubLocation[] Returns an array of ClubLocation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('c.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?ClubLocation
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
