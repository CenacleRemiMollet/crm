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

class ClubPricesController extends AbstractController
{

    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
	/**
	 * @Route("/club/{uuid}/prices", name="web_view_club_locations", methods={"GET"})
	 */
    public function getPrices(string $uuid, Request $request, SessionInterface $session)
	{
	    $clubs = $this->container->get('doctrine')->getManager()
    	    ->getRepository(Club::class)
    	    ->findBy(['uuid' => $uuid]);
	    if(empty($clubs)) {
	        return $this->render('club/club-not-found.html.twig', []);
	    }
	    $club = $clubs[0];
	    
	    $clubAccess = new ClubAccess($this->container, $this->logger);
	    if(! $clubAccess->hasAccessForUser($club, $this->getUser())) {
	        return $this->render('security/unauthorized.html.twig', []);
	    }
	    
	    $response = $this->forward('App\Controller\Api\ClubPricesController::getPrices', ["club_uuid" => $uuid]);
		$json = json_decode($response->getContent());
		return $this->render('club/config-prices.html.twig', [
		    'club' => $club,
		    'prices' => $json
		]);
	}

}
