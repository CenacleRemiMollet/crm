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
use Symfony\Component\HttpFoundation\Response;
use App\Entity\EntityFinder;
use App\Entity\ClubLocation;

class ClubLocationsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
	/**
	 * @Route("/club/{club_uuid}/locations", name="web_view_club_locations", methods={"GET"})
	 */
    public function getLocations(string $club_uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $response = $this->forward('App\Controller\Api\ClubLocationsController::getLocations', ["club_uuid" => $club_uuid]);
		$json = json_decode($response->getContent());
		return $this->render('club/config-locations.html.twig', [
		    'club' => $club,
		    'locations' => $json
		]);
	}

	
	/**
	 * @Route("/club/{club_uuid}/locations/{location_uuid}", name="web_view_club_location", methods={"GET"})
	 */
	public function getLocation(string $club_uuid, string $location_uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $entityFinder->findOneByOrThrow(ClubLocation::class, ['uuid' => $location_uuid, 'club' => $club]); // 404
	    
	    $locationResponse = $this->forward('App\Controller\Api\ClubLocationsController::getLocation', ["club_uuid" => $club_uuid, "location_uuid" => $location_uuid]);
	    return $this->render('club/config-location.html.twig', [
	        'club' => $club,
	        'location' => json_decode($locationResponse->getContent())
	    ]);
	}

	/**
	 * @Route("/club/{club_uuid}/location-new", name="web_new_club_location", methods={"GET"})
	 */
	public function getLocationNew(string $club_uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club_uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    return $this->render('club/config-location-new.html.twig', [
	        'club' => $club
	    ]);
	}
	
}
