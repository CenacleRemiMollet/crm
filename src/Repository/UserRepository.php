<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use App\Security\Roles;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]	findAll()
 * @method User[]	findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements LoggerAwareInterface
{
	
    private LoggerInterface $logger;
    
    public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	public function setLogger(LoggerInterface $logger): void
	{
	    $this->logger = $logger;
	}
	
	public function findInAll($uuid = null, $q = null, $offset = 0, $limit = 20)
	{
		$sql = $this->prepareUserAccountSelect()
			  ." FROM user u"
			  ."  LEFT JOIN account a ON a.user_id = u.id";
	    if($uuid !== null && $q !== null) {
	        $sql .= " WHERE u.uuid = :uuid AND ".$this->appendFilter();
	    } elseif($uuid !== null) {
	        $sql .= " WHERE u.uuid = :uuid";
	    } elseif($q !== null) {
	        $sql .= " WHERE ".$this->appendFilter();
	    }
		$sql .= " ORDER BY lastname ASC, firstname ASC";
		$sql .= " LIMIT ".$offset.", ".$limit;

		$rsm = $this->prepareUserAccountMapping();
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		if($uuid) {
			$query->setParameter('uuid', $uuid);
		}
		if($q) {
		    $query->setParameter('query', '%'.$q.'%');
		}
		
		//$this->logger->debug($query->getSQL());
		return $query->getResult();
	}

	public function findInMyClubs($accountId, ?string $uuid = null, ?string $q = null, $offset = 0, $limit = 20)
	{
		$sql = $this->prepareUserAccountSelect()
			  .$this->joinInMyClubs()
			  ." WHERE act.id = :accountId";
		if($uuid) {
			$sql = $sql." AND u.uuid = :uuid";
		}
		if($q) {
		    $sql = $sql." AND ".$this->appendFilter();
		}
		$sql .= " ORDER BY lastname ASC, firstname ASC";
		$sql .= " LIMIT ".$offset.", ".$limit;

		$rsm = $this->prepareUserAccountMapping();
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter('accountId', $accountId);
		if($uuid) {
			$query->setParameter('uuid', $uuid);
		}
		if($q) {
		    $query->setParameter('query', '%'.$q.'%');
		}
		return $query->getResult();
	}

	// /**
	//  * @return User[] Returns an array of User objects
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
	public function findOneBySomeField($value): ?User
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}*/

	public static function joinInMyClubs() {
		return" FROM account act"
			  ."  JOIN user teacher ON act.user_id = teacher.id"
			  ."  JOIN user_club_subscribe tsubsc ON ("
			  ."        teacher.id = tsubsc.user_id"
			  ."         AND (json_contains(tsubsc.roles, json_quote('".Roles::ROLE_TEACHER."')) OR json_contains(tsubsc.roles, json_quote('".Roles::ROLE_CLUB_MANAGER."')))"
			  ."        )"
			  ."  JOIN user_club_subscribe usubsc ON (tsubsc.club_id = usubsc.club_id OR tsubsc.id = usubsc.id)"
			  ."  JOIN user u ON u.id = usubsc.user_id"
			  ."  LEFT JOIN account a ON a.user_id = u.id";
	}

	//*****************************************************************

	private function prepareUserAccountSelect() {
		return "SELECT u.id AS u_id, u.uuid AS u_uuid, u.lastname, u.firstname, u.birthday,"
			  ."	   u.sex, u.address, u.zipcode, u.city, u.phone, u.phone_emergency,"
			  ."	   u.nationality, u.mails, u.created, u.blacklist_date, u.blacklist_reason,"
			  ."	   a.id AS a_id, a.login, a.roles, a.has_access";
	}

	private function prepareUserAccountMapping() {
		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
		$rsm->addRootEntityFromClassMetadata('App\Entity\User', 'u');
		$rsm->addFieldResult('u', 'u_id', 'id');
		$rsm->addFieldResult('u', 'u_uuid', 'uuid');
		$rsm->addFieldResult('u', 'lastname', 'lastname');
		$rsm->addFieldResult('u', 'firstname', 'firstname');
		$rsm->addFieldResult('u', 'birthday', 'birthday');
		$rsm->addFieldResult('u', 'sex', 'sex');
		$rsm->addFieldResult('u', 'address', 'address');
		$rsm->addFieldResult('u', 'zipcode', 'zipcode');
		$rsm->addFieldResult('u', 'city', 'city');
		$rsm->addFieldResult('u', 'phone', 'phone');
		$rsm->addFieldResult('u', 'phone_emergency', 'phone_emergency');
		$rsm->addFieldResult('u', 'nationality', 'nationality');
		$rsm->addFieldResult('u', 'mails', 'mails');
		$rsm->addFieldResult('u', 'created', 'created');
		$rsm->addFieldResult('u', 'blacklist_date', 'blacklist_date');
		$rsm->addFieldResult('u', 'blacklist_reason', 'blacklist_reason');
		//$rsm->addJoinedEntityFromClassMetadata('App\Entity\Account', 'a', 'u', 'accounts');
		$rsm->addJoinedEntityResult('App\Entity\Account', 'a', 'u', 'accounts');
		$rsm->addFieldResult('a', 'a_id', 'id');
		$rsm->addFieldResult('a', 'login', 'login');
		$rsm->addFieldResult('a', 'roles', 'roles');
		$rsm->addFieldResult('a', 'has_access', 'has_access');
		return $rsm;
	}
	
	private function appendFilter(): string
	{
	   return "(remove_accents(lower(lastname)) LIKE :query"
	        ."    OR remove_accents(lower(firstname)) LIKE :query"
	        ."    OR remove_accents(lower(mails)) LIKE :query"
	        ."    OR remove_accents(lower(address)) LIKE :query"
            ."    OR remove_accents(lower(city)) LIKE :query"
            ."    OR remove_accents(lower(nationality)) LIKE :query"
            ."    OR remove_accents(lower(login)) LIKE :query)";
	}

}
