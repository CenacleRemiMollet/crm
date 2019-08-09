<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]	findAll()
 * @method User[]	findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
	public function __construct(RegistryInterface $registry)
	{
		parent::__construct($registry, User::class);
	}

	public function findInAll($uuid = null, $offset = 0, $limit = 20)
	{
		$sql = $this->prepareUserAccountSelect()
			  ." FROM user u"
			  ."  LEFT JOIN account a ON a.user_id = u.id";
		if($uuid) {
			$sql .= " WHERE u.uuid = :uuid";
		}
		$sql .= " ORDER BY lastname ASC, firstname ASC";
		$sql .= " LIMIT ".$offset.", ".$limit;

		$rsm = $this->prepareUserAccountMapping();
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		if($uuid) {
			$query->setParameter('uuid', $uuid);
		}
		return $query->getResult();
	}

	public function findInMyClubs($accountId, $uuid = null, $offset = 0, $limit = 20)
	{
		$sql = $this->prepareUserAccountSelect()
			  ." FROM account act"
			  ."  JOIN user teacher ON act.user_id = teacher.id"
			  ."  JOIN user_club_subscribe tsubsc ON (teacher.id = tsubsc.user_id AND json_contains(tsubsc.roles, json_quote('CLUB_TEACHER')))"
			  ."  JOIN user_club_subscribe usubsc ON (tsubsc.club_id = usubsc.club_id AND (NOT json_contains(usubsc.roles, json_quote('CLUB_TEACHER'))) OR tsubsc.id = usubsc.id)"
			  ."  JOIN user u ON u.id = usubsc.user_id"
			  ."  LEFT JOIN account a ON a.user_id = u.id"
			  ." WHERE act.id = :accountId";
		if($uuid) {
			$sql = $sql." AND u.uuid = :uuid";
		}
		$sql .= " ORDER BY lastname ASC, firstname ASC";
		$sql .= " LIMIT ".$offset.", ".$limit;

		$rsm = $this->prepareUserAccountMapping();
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter('accountId', $accountId);
		if($uuid) {
			$query->setParameter('uuid', $uuid);
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

}
