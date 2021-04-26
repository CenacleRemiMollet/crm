<?php

namespace App\Repository;

use App\Entity\ClubLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use App\Model\ClubLocationView;
use App\Model\ClubView;
use Doctrine\Common\Persistence\ManagerRegistry;

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

	public function findByZipcodeAndDistance($zipcode, $distance) {
		$sql = "SELECT cl.*"
				." FROM club_location cl"
				."  JOIN ("
				."   SELECT clist.zip_code,"
				."          (6371 * acos( cos(radians(cref.latitude)) * cos(radians(clist.latitude)) * cos(radians(clist.longitude) - radians(cref.longitude)) + sin(radians(cref.latitude)) * sin(radians(clist.latitude)))) AS distance"
				."    FROM city clist, (SELECT * FROM city WHERE zip_code = :zipcode LIMIT 1) cref"
				."    HAVING distance < :distance"
				."  ) c ON cl.zipcode = c.zip_code";
		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
		$rsm->addRootEntityFromClassMetadata('App\Entity\ClubLocation', 'l');
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter('zipcode', $zipcode);
		$query->setParameter('distance', $distance);
		return $query->getResult();
	}
	
	
	public function findByClubs($clubs) {
		$clubByIds = array();
		$clubIds = array();
		foreach ($clubs as &$club) {
			$clubByIds[$club->getId()] = $club;
			array_push($clubIds, $club->getId());
		}

		$result = $this::findByClubIds($clubIds);

		$loclist = array();
		foreach($result as &$r) {
			$cid = $r['c'];
			$loc = new ClubLocationView($r[0]);
			if(! array_key_exists($cid, $loclist)) {
				$loclist[$cid] = [$loc];
			} else {
				array_push($loclist[$cid], $loc);
			}
		}
		$output = array();
		foreach($clubByIds as $cid => $club) {
			$locs = array_key_exists($cid, $loclist) ? $loclist[$cid] : [];
			array_push($output, new ClubView($club, $locs));
		}

		return $output;
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
