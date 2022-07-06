<?php
namespace App\Dao;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Account;
use App\Model\SearchResultView;
use Doctrine\ORM\Query\ResultSetMapping;
use App\Repository\UserRepository;
use App\Util\Pageable;

class SearchDao
{

    private $em;
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $em, $authorizationChecker)
    {
        $this->em = $em;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function search($query, ?Account $connectedAccount, Pageable $pageable = null) {
        $paramLC = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
            ->transliterate($query);
        $paramLC = '%'.mb_strtolower($paramLC).'%';

        $unions = array($this::inClubAll());
        $params = array('query' => $paramLC);
        if($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            array_push($unions, $this::inUserAll());
        } elseif($this->authorizationChecker->isGranted("ROLE_CLUB_MANAGER")) {
            array_push($unions, $this::inUserInMyClubs());
            $params['teacherAccountId'] = $connectedAccount->getId();
        }

        $sql = "SELECT *"
               ." FROM ("
               .implode(" UNION ", $unions)
               .") t"
               ." ORDER BY 3"; // variable : ASC / DESC
        if($pageable !== null && $pageable->isPaged()) {
            $sql .= " LIMIT ".$pageable->getOffset().", ".($pageable->getPageSize() + 2);
        }
               
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('uuid', 'uuid');
        $rsm->addScalarResult('name', 'name');

        $stmt = $this->em->createNativeQuery($sql, $rsm);
        foreach($params as $paramK => $paramV) {
        	$stmt->setParameter($paramK, $paramV);
        }

        //$stmt = $this->em->getConnection()->executeQuery($sql, $params);
        $results = $stmt->getResult();
        $output = array();
        foreach(array_slice($results, 0, $pageable->getPageSize())  as $result) {
         	array_push($output, new SearchResultView(
        		$result['type'],
        		$result['uuid'],
        		$result['name']));
        }
        return [
            // TODO use Pageable
        	'query' => $query,
            'offset' => $pageable->getOffset(),
            'limit' => $pageable->getPageSize(),
        	'results' => $output,
            'hasmore' => count($results) > $pageable->getPageSize()
        ];
    }


    private function inUserAll()
    {
        return "SELECT 'user' AS type, u.uuid, CONCAT(lastname, ' ', firstname) AS name"
              ." FROM user u"
              ."  LEFT JOIN account a ON a.user_id = u.id"
              ." WHERE (remove_accents(lower(lastname)) LIKE :query"
              ."    OR remove_accents(lower(firstname)) LIKE :query"
              ."    OR remove_accents(lower(mails)) LIKE :query"
              ."    OR remove_accents(lower(address)) LIKE :query"
              ."    OR remove_accents(lower(city)) LIKE :query"
              ."    OR remove_accents(lower(nationality)) LIKE :query"
              ."    OR remove_accents(lower(login)) LIKE :query)"
              ;
    }

    private function inUserInMyClubs()
    {
        return "SELECT 'user' AS type, u.uuid, CONCAT(u.lastname, ' ', u.firstname) AS name"
              .UserRepository::joinInMyClubs()
              ." WHERE act.id = :teacherAccountId"
              ."   AND (remove_accents(lower(u.lastname)) LIKE :query"
              ."     OR remove_accents(lower(u.firstname)) LIKE :query"
              ."     OR remove_accents(lower(u.mails)) LIKE :query"
              ."     OR remove_accents(lower(u.address)) LIKE :query"
              ."     OR remove_accents(lower(u.city)) LIKE :query"
              ."     OR remove_accents(lower(u.nationality)) LIKE :query"
              ."     OR remove_accents(lower(a.login)) LIKE :query"
              ."   	 )"
              ." GROUP BY 1, 2, 3"
              ;
    }

    private function inClubAll()
    {
        return "SELECT 'club' AS type, c.uuid, c.name"
              ." FROM club c"
              ."  JOIN club_lesson cles ON (cles.club_id = c.id AND c.active)"
              ."  JOIN club_location cl ON cles.club_location_id = cl.id"
              ." WHERE remove_accents(lower(c.name)) LIKE :query"
              ."    OR remove_accents(lower(cl.name)) LIKE :query"
              ."    OR remove_accents(lower(cl.city)) LIKE :query"
              ."    OR remove_accents(lower(cl.address)) LIKE :query"
              ."    OR remove_accents(lower(cl.zipcode)) LIKE :query"
              ."    OR remove_accents(lower(cl.county)) LIKE :query"
              ."    OR remove_accents(lower(cl.country)) LIKE :query"
              ."    OR remove_accents(lower(cles.discipline)) LIKE :query"
              ."    OR remove_accents(lower(cles.age_level)) LIKE :query"
              ." GROUP BY 1, 2, 3"
              ;
    }
}

