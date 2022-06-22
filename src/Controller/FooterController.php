<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\ConfigurationPropertyService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\EntityFinder;
use App\Security\ClubAccess;
use App\Entity\Club;
use Psr\Log\LoggerInterface;

class FooterController extends AbstractController
{
    
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function viewFooter(SessionInterface $session)
	{
	    $doctrine = $this->container->get('doctrine');
	    $propService = new ConfigurationPropertyService($doctrine->getManager());
	    $cenacleProperties = $propService->findStartsWithToMap('cenacle.');
	    $clubProperties = $propService->findStartsWithToMap('club.');
	    $club = $session->get('club-selected');
	    
	    $canConfigure = false;
	    if($club !== null) {
	        $entityFinder = new EntityFinder($doctrine);
	        $clubObj = $entityFinder->findOneByOrThrow(Club::class, ['uuid' => $club->uuid]); // 404, never happen !
	        
	        $clubAccess = new ClubAccess($this->container, $this->logger);
	        $canConfigure = $clubAccess->hasAccessForUser($clubObj, $this->getUser());
	    }
	    
	    return $this->render('modules/footer.html.twig', [
	        'cenacleProperties' => $cenacleProperties,
	        'clubProperties' => $clubProperties,
	        'club' => $club,
	        'canConfigure' => $canConfigure
	    ]);
	}
}
