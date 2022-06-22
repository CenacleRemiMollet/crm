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

class ClubLocationsController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
	/**
	 * @Route("/club/{uuid}/locations", name="web_view_club_locations", methods={"GET"})
	 */
    public function getLocations(string $uuid, Request $request, SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    
	    $entityFinder = new EntityFinder($doctrine);
	    $club = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $uuid]); // 404
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    $clubAccess->checkAccessForUser($club, $this->getUser()); // 403
	    
	    $response = $this->forward('App\Controller\Api\ClubLocationsController::getLocations', ["club_uuid" => $uuid]);
		$json = json_decode($response->getContent());
		return $this->render('club/config-locations.html.twig', [
		    'club' => $club,
		    'locations' => $json
		]);
	}

}
