<?php
namespace App\Dao;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Account;
use App\Model\SearchResultView;
use Doctrine\ORM\Query\ResultSetMapping;

class SearchDao
{

    private $em;
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $em, $authorizationChecker)
    {
        $this->em = $em;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function search($query, ?Account $connectedAccount, $offset = 0, $limit = 20) {
        $paramLC = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
            ->transliterate($query);
        $paramLC = '%'.mb_strtolower($paramLC).'%';

        $unions = array($this::inClub());
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
               ." ORDER BY 3" // variable : ASC / DESC
               ." LIMIT ".$offset.", ".($limit + 1);

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
        foreach(array_slice($results, 0, $limit)  as $result) {
         	array_push($output, new SearchResultView(
        		$result['type'],
        		$result['uuid'],
        		$result['name']));
        }
        return [
        	'query' => $query,
        	'offset' => $offset,
        	'limit' => $limit,
        	'results' => $output,
        	'hasmore' => count($results) > $limit
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
              ." FROM account a"
              ."  JOIN user teacher ON a.user_id = teacher.id"
              ."  JOIN user_lesson_subscribe tsubsc ON teacher.id = tsubsc.user_id"
              ."  JOIN user_lesson_subscribe usubsc ON tsubsc.lesson_id = usubsc.lesson_id"
              ."  JOIN user u ON u.id = usubsc.user_id"
              ."  LEFT JOIN account au ON au.user_id = u.id"
              ." WHERE a.id = :teacherAccountId"
              ."   AND (remove_accents(lower(u.lastname)) LIKE :query"
              ."     OR remove_accents(lower(u.firstname)) LIKE :query"
              ."     OR remove_accents(lower(u.mails)) LIKE :query"
              ."     OR remove_accents(lower(u.address)) LIKE :query"
              ."     OR remove_accents(lower(u.city)) LIKE :query"
              ."     OR remove_accents(lower(u.nationality)) LIKE :query"
              ."     OR remove_accents(lower(au.login)) LIKE :query"
              ."   	 )"
              ." GROUP BY 1, 2, 3"
              ;
    }

    private function inClub()
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

