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
use App\Entity\EntityFinder;
use App\Entity\Club;
use App\Security\ClubAccess;
use App\Service\ClubService;
use Hateoas\HateoasBuilder;
use App\Security\Roles;

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
	 * @Route("/clubs", name="web_clubs_list", methods={"GET"})
	 */
	public function getAllClubs(Request $request, SessionInterface $session)
	{
	    if(! $this->isGranted(Roles::ROLE_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_SUPER_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_CLUB_MANAGER)
	        && ! $this->isGranted(Roles::ROLE_TEACHER)) {
	            throw $this->createAccessDeniedException();    
	    }
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubs = $clubAccess->getClubsForAccount($this->getUser());
	    
	    $clubService = new ClubService($this->container->get('doctrine'));
	    $clubViews = $clubService->convertToView($clubs);
	   
	    $hateoas = HateoasBuilder::create()->build();
	    
	    return $this->render('club/clubs.html.twig', [
	        'clubs' => json_decode($hateoas->serialize($clubViews, 'json'))
	    ]);
	}
	
	/**
	 * @Route("/club/{uuid}", name="web_club_one", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewOne($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
			throw $this->createNotFoundException();
		}
		$club = json_decode($response->getContent());
		$session->set('club-selected', $club);

		$response = $this->forward('App\Controller\Api\ClubLessonsController::getLessons', ['club_uuid' => $uuid]);
		$lessons = json_decode($response->getContent());
		$session->set('lessons-selected', $lessons);

		return $this->render('club/club.html.twig', [
			'club' => $club,
			'lessons' => $lessons,
		    'canConfigure' => $this->canConfigure($uuid)
		]);
	}
	

	/**
	 * @Route("/club/{uuid}/infos", name="web_club_infos", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function viewInfos($uuid, LoggerInterface $logger, SessionInterface $session)
	{
		$response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
		if($response->getStatusCode() != 200) {
		    throw $this->createNotFoundException();
		}
		$club = json_decode($response->getContent());
		$session->set('club-selected', $club);

		$response = $this->forward('App\Controller\Api\ClubLessonsController::getLessons', ['club_uuid' => $uuid]);
		$lessons = json_decode($response->getContent());
		$session->set('lessons-selected', $lessons);
		
		return $this->render('club/club-infos.html.twig', [
			'club' => $club,
			'lessons' => $lessons,
		    'startTimeByDays' => $this->determineStartsOffsetByQuarter($lessons),
		    'canConfigure' => $this->canConfigure($uuid)
		]);
	}

	
	/**
	 * @Route("/club-new", name="web_new_club", methods={"GET"})
	 */
	public function create()
	{
	    $this->denyAccessUnlessGranted(Roles::ROLE_ADMIN);
	    return $this->render('club/club-new.html.twig', []);
	}
	
	
	/**
	 * @Route("/club/{uuid}/modify", name="web_modify_club", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function modifyOne($uuid, LoggerInterface $logger, SessionInterface $session)
	{
	    if(! $this->isGranted(Roles::ROLE_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_SUPER_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_CLUB_MANAGER)
	        && ! $this->isGranted(Roles::ROLE_TEACHER)) {
	        throw $this->createAccessDeniedException();
	    }
	     
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
	    if($response->getStatusCode() != 200) {
	        throw $this->createNotFoundException();
	    }
	    $club = json_decode($response->getContent());
	    $session->set('club-selected', $club);
	    
	    return $this->render('club/club-modify.html.twig', [
	        'club' => $club
	    ]);
	}

	
	/**
	 * @Route("/club/{uuid}/logo/modify", name="web_modify_club_logo", methods={"GET"}, requirements={"uuid"="[a-z0-9_]{2,64}"})
	 */
	public function modifyLogo($uuid, LoggerInterface $logger, SessionInterface $session)
	{
	    if(! $this->isGranted(Roles::ROLE_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_SUPER_ADMIN)
	        && ! $this->isGranted(Roles::ROLE_CLUB_MANAGER)
	        && ! $this->isGranted(Roles::ROLE_TEACHER)) {
	        throw $this->createAccessDeniedException();
	    }
	        
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $response = $this->forward('App\Controller\Api\ClubController::one', ['uuid' => $uuid]);
	    if($response->getStatusCode() != 200) {
	        throw $this->createNotFoundException();
	    }
	    $club = json_decode($response->getContent());
	    $session->set('club-selected', $club);
	    
	    return $this->render('club/club-logo-modify.html.twig', [
	        'club' => $club
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
	
	private function canConfigure($club_uuid): bool
	{
	    $doctrine = $this->container->get('doctrine');
	    $entityFinder = new EntityFinder($doctrine);
	    $clubObj = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404, never happen !
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	   return $clubAccess->hasAccessForUser($clubObj, $this->getUser());
	    
	}
}
