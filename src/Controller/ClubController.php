<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Util\DateIntervalUtils;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ClubPrice;

class ClubController extends AbstractController
{

    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    
	/**
	 * @Route("/club", name="web_club_list-active", methods={"GET"})
	 */
	public function listActive(Request $request, SessionInterface $session)
	{
		if($request->query->get('select') === 'clear') {
			$session->remove('club-selected');
			$session->remove('lessons-selected');
		}

		$response = $this->forward('App\Controller\Api\ClubSearchController::search', ["request" => $request]);
		$json = json_decode($response->getContent());
		return $this->render('club/club-list.html.twig', [
			'clubs' => $json
		]);
	}

	/**
	 * @Route("/club/{uuid}", name="web_club_one", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewOne($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			return $this->render('club/club-not-found.html.twig', []);
		}
		$club = json_decode($response->getContent());
		$session->set('club-selected', $club);

		$response = $this->forward('App\Controller\Api\ClubLessonsController::getLessons', ['uuid' => $uuid]);
		$lessons = json_decode($response->getContent());
		$session->set('lessons-selected', $lessons);

		return $this->render('club/club.html.twig', [
			'club' => $club,
			'lessons' => $lessons
		]);
	}

	/**
	 * @Route("/club/{uuid}/infos", name="web_club_infos", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewInfos($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			return $this->render('club/club-not-found.html.twig', []);
		}
		$club = json_decode($response->getContent());
		$session->set('club-selected', $club);

		$response = $this->forward('App\Controller\Api\ClubLessonsController::getLessons', ['uuid' => $uuid]);
		$lessons = json_decode($response->getContent());
		$session->set('lessons-selected', $lessons);
		
		//$managerRegistry->getRepository(ClubPrice::class)->findBy(["club_id" => $club->getId()]);
		//$price = $this->getDoctrine()->getManager()->getRepository(ClubPrice::class)->findBy(["club_id" => $club['id']]);

		return $this->render('club/club-infos.html.twig', [
			'club' => $club,
			'lessons' => $lessons,
		    'startTimeByDays' => $this->determineStartsOffsetByQuarter($lessons)
		]);
	}
	
	//************************************************
	
	private function determineStartsOffsetByQuarter($lessons)
	{
	    if($lessons === null) {
	        return array();
	    }
	    $startTimeMinuteByDays = array();
	    foreach($lessons as &$lesson) {
	        $startMinutes = DateIntervalUtils::getTotalMinutes(DateIntervalUtils::parseHourDoubleDotsMinute($lesson->start_time));
	        if(array_key_exists($lesson->day_of_week, $startTimeMinuteByDays)) {
	            $startTimeMinuteByDays[$lesson->day_of_week] = min($startMinutes, $startTimeMinuteByDays[$lesson->day_of_week]);
	        } else {
	            $startTimeMinuteByDays[$lesson->day_of_week] = $startMinutes;
	        }
	    }
	    $minStartMinutes = min($startTimeMinuteByDays);
	    
	    $startOffsetByDays = array();
	    foreach ($startTimeMinuteByDays as $day => $minutes) {
	        $startOffsetByDays[$day] = ceil(($minutes - $minStartMinutes) / 15);
	    }
	    asort($startOffsetByDays);
	    $previous = 0;
	    $diff = 0;
	    foreach ($startOffsetByDays as $day => $minutes) {
	        if($minutes != 0) {
	            $d = $minutes - $previous;
	            if($d > 1) {
	                $diff = $d - 1;
	            }
	            $previous = $startOffsetByDays[$day];
	            $startOffsetByDays[$day] = $startOffsetByDays[$day] - $diff;
	        }
	    }
	    return $startOffsetByDays;
	}
}
