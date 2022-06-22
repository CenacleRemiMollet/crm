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

class ClubLessonsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
	/**
	 * @Route("/club/{uuid}/lessons", name="web_view_club_lessons", methods={"GET"})
	 */
    public function getPrices(string $uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $lessonsResponse = $this->forward('App\Controller\Api\ClubLessonsController::getLessons', ["club_uuid" => $uuid]);
	    $locationsResponse = $this->forward('App\Controller\Api\ClubLocationsController::getLocations', ["club_uuid" => $uuid]);
		return $this->render('club/config-lessons.html.twig', [
		    'club' => $club,
		    'lessons' => json_decode($lessonsResponse->getContent()),
		    'locations' => json_decode($locationsResponse->getContent())
		]);
	}

}
