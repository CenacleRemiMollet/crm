<?php

namespace App\Repository;

use App\Entity\ClubLesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method ClubLesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClubLesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClubLesson[]    findAll()
 * @method ClubLesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClubLessonRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubLesson::class);
    }

    /**
     * @return ClubLesson[] Returns an array of ClubLesson objects
     */
    public function findByClubUuid_TODEL($clubUuid)
    {
		$sql = "SELECT "//*, loc.uuid AS club_location_uuid, loc.name AS club_location_name,"
				."  cl.id AS clid, cl.uuid AS cluuid, cl.club_location_id AS clclub_location_id, cl.club_id AS clclub_id, cl.point AS clpoint, cl.discipline AS cldiscipline, cl.age_level  AS clage_level, cl.day_of_week AS clday_of_week, cl.start_time AS clstart_time, cl.end_time AS clend_time,"
				."  c.id AS cid, c.uuid AS cuuid, c.name AS cname, c.logo AS clogo, c.website_url AS cwebsite_url, c.facebook_url AS cfacebook_url, c.mailing_list AS cmailing_list, c.active AS cactive"
				." FROM club_lesson cl"
				."  JOIN club c ON c.id = cl.club_id"
				."  JOIN club_location loc ON cl.club_location_id = loc.id"
				." WHERE c.uuid = :clubUuid";
		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
		//$rsm->addRootEntityFromClassMetadata('App\Entity\ClubLesson', 'l');
		//$rsm->addJoinedEntityFromClassMetadata('App\Entity\Club', 'c', 'l', 'club', ['id' => 'cid', 'uuid' => 'cuuid']);
		//$rsm->addJoinedEntityFromClassMetadata('App\Entity\ClubLocation', 'loc', 'l', 'club_location', ['id' => 'club_location_id', 'uuid' => 'club_location_uuid', 'name' => 'club_location_name']);
		$rsm->addEntityResult('App\Entity\ClubLesson', 'cl');
		$rsm->addFieldResult('cl', 'clid', 'id');
		$rsm->addFieldResult('cl', 'cluuid', 'uuid');
		//$rsm->addFieldResult('cl', 'clclub_location_id', 'uuid');
		//$rsm->addFieldResult('cl', 'clclub_id', 'uuid');
		$rsm->addFieldResult('cl', 'clpoint', 'point');
		$rsm->addFieldResult('cl', 'cldiscipline', 'discipline');
		$rsm->addFieldResult('cl', 'clage_level', 'age_level');
		$rsm->addFieldResult('cl', 'clday_of_week', 'day_of_week');
		$rsm->addFieldResult('cl', 'clstart_time', 'start_time');
		$rsm->addFieldResult('cl', 'clend_time', 'end_time');

// 		$rsm->addEntityResult('App\Entity\Club', 'c');
// 		$rsm->addFieldResult('c', 'cid', 'id');
// 		$rsm->addFieldResult('c', 'cuuid', 'uuid');
// 		$rsm->addFieldResult('c', 'cname', 'name');
// 		$rsm->addFieldResult('c', 'clogo', 'logo');
// 		$rsm->addFieldResult('c', 'cwebsite_url', 'website_url');
// 		$rsm->addFieldResult('c', 'cfacebook_url', 'facebook_url');
// 		$rsm->addFieldResult('c', 'cmailing_list', 'mailing_list');
//		$rsm->addFieldResult('c', 'cactive', 'active');

		//$this->getEntityManager()->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

		dump($this->getEntityManager()->getClassMetadata('App\Entity\ClubLesson')->getFieldNames());
		dump($this->getEntityManager()->getClassMetadata('App\Entity\ClubLesson')->getAssociationNames());



		$query->setParameter('clubUuid', $clubUuid);
		dump($sql);
		dump($query->getResult());
		//return $query->getResult(\Doctrine\ORM\Query::HYDRATE_SIMPLEOBJECT);
		return $query->getResult();
	}

	public function findByClubUuid($clubUuid)
	{
	    $dql = "SELECT cl, c, cloc".
               " FROM App\Entity\ClubLesson cl".
               "  JOIN cl.club c".
               "  JOIN cl.club_location cloc".
               " WHERE c.uuid = :clubUuid".
               " ORDER BY FIELD(cl.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cl.start_time";
	    $query = $this->getEntityManager()->createQuery($dql);
	    $query->setParameter('clubUuid', $clubUuid);
	    return $query->getResult();
	}

}
