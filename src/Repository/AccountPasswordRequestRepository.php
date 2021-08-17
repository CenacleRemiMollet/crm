<?php

namespace App\Repository;

use App\Entity\AccountPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountPasswordRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountPasswordRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountPasswordRequest[]	findAll()
 * @method AccountPasswordRequest[]	findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountPasswordRequestRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, AccountPasswordRequest::class);
	}

	public function buildByValidLogin($login): ?AccountPasswordRequest
	{
		$sql = "SELECT a.*,"
			  ."       apr.id AS aid, apr.account_id AS aaid, apr.uuid AS auuid, apr.create_date AS acd"
			  ." FROM user u"
			  ."  JOIN account a ON u.id = a.user_id"
			  ."  LEFT JOIN account_password_request apr ON apr.account_id = a.id"
			  ." WHERE login = :login"
			  ."  AND blacklist_date IS NULL"
			  ."  AND a.has_access";

		$rsm = new ResultSetMappingBuilder($this->getEntityManager());
		$rsm->addRootEntityFromClassMetadata('App\Entity\Account', 'a');
		$rsm->addScalarResult('aid', 'aid');
		$rsm->addScalarResult('aaid', 'aaid');
		$rsm->addScalarResult('auuid', 'auuid');
		$rsm->addScalarResult('acd', 'acd', 'datetime');
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter('login', $login);
		$result = $query->getResult();
		if(count($result) == 1) {
			$request = new AccountPasswordRequest();
			$request->setAccount($result[0][0]);
			$apr_id = $result[0]['aid'];
			if($apr_id != null) {
				$request
				->setCreateDate($result[0]['acd'])
				->setId($apr_id)
				->setUuid($result[0]['auuid']);
			} else {
				$this->getEntityManager()->persist($request);
				$this->getEntityManager()->flush();
			}
			return $request;
		}
		return null;
	}


	// /**
	//  * @return AccountPasswordRequest[] Returns an array of AccountPasswordRequest objects
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
	public function findOneBySomeField($value): ?AccountPasswordRequest
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
