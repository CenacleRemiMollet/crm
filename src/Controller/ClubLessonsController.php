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
use App\Entity\Club;
use App\Security\ClubAccess;
use App\Entity\EntityFinder;
use App\Entity\ClubLesson;

class ClubLessonsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
	/**
	 * @Route("/club/{club_uuid}/lessons", name="web_view_club_lessons", methods={"GET"})
	 */
    public function getLessons(string $club_uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $lessonsResponse = $this->forward('App\Controller\Api\ClubLessonsController::getLessons', ["club_uuid" => $club_uuid]);
	    $locationsResponse = $this->forward('App\Controller\Api\ClubLocationsController::getLocations', ["club_uuid" => $club_uuid]);
		return $this->render('club/config-lessons.html.twig', [
		    'club' => $club,
		    'lessons' => json_decode($lessonsResponse->getContent()),
		    'locations' => json_decode($locationsResponse->getContent())
		]);
	}

	/**
	 * @Route("/club/{club_uuid}/lessons/{lesson_uuid}", name="web_view_club_lesson", methods={"GET"})
	 */
	public function getLesson(string $club_uuid, string $lesson_uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $entityFinder->findOneByOrThrow(ClubLesson::class, ['uuid' => $lesson_uuid, 'club' => $club]); // 404
	    
	    $lessonResponse = $this->forward('App\Controller\Api\ClubLessonsController::getLesson', ["club_uuid" => $club_uuid, "lesson_uuid" => $lesson_uuid]);
	    $locationsResponse = $this->forward('App\Controller\Api\ClubLocationsController::getLocations', ["club_uuid" => $club_uuid]);
	    //$this->logger->debug('getLesson response: '.$lessonResponse->getStatusCode().'  '.$lessonResponse->getContent());
	    
	    return $this->render('club/config-lesson.html.twig', [
	        'club' => $club,
	        'lesson' => json_decode($lessonResponse->getContent()),
	        'locations' => json_decode($locationsResponse->getContent())
	    ]);
	}

	/**
	 * @Route("/club/{club_uuid}/lesson-new", name="web_new_club_lesson", methods={"GET"})
	 */
	public function getLessonNew(string $club_uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $locationsResponse = $this->forward('App\Controller\Api\ClubLocationsController::getLocations', ["club_uuid" => $club_uuid]);
	    
	    return $this->render('club/config-lesson-new.html.twig', [
	        'club' => $club,
	        'locations' => json_decode($locationsResponse->getContent())
	    ]);
	}
	
}
